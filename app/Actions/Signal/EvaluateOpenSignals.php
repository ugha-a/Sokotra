<?php

namespace App\Actions\Signal;

use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Enums\SignalStatus;
use App\Events\SignalClosed;
use App\Events\SignalTriggered;
use App\Models\Instrument;
use App\Models\InstrumentFeedCursor;
use App\Models\Signal;
use App\Services\PriceFeed\Candle;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\PriceFeedUnavailable;
use App\Support\Settings;
use App\Support\Signal\PriceMath;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Scores open signals from 1-minute bid candles (docs/logic/signal-lifecycle.md → Engine Evaluasi).
 *
 * Rules: only candles that start at or after publish time count; buys exit on the bid
 * and sells on the ask (bid + spread snapshot); if one candle touches both TP and SL the
 * signal.ambiguous_candle_rule setting decides. When the feed fails nothing is guessed.
 */
class EvaluateOpenSignals
{
    /** @var array<int, string> Last bid close seen per signal, used for timeouts. */
    private array $lastClose = [];

    public function __construct(private PriceFeed $feed) {}

    /**
     * @return array{instruments: int, closed: int, failed: list<string>}
     */
    public function handle(): array
    {
        $summary = ['instruments' => 0, 'closed' => 0, 'failed' => []];
        $instrumentIds = Signal::open()->distinct()->pluck('instrument_id');

        foreach (Instrument::whereIn('id', $instrumentIds)->get() as $instrument) {
            $summary['instruments']++;

            try {
                $summary['closed'] += $this->evaluateInstrument($instrument);
            } catch (PriceFeedUnavailable $exception) {
                $summary['failed'][] = $instrument->symbol;
                Log::warning("Signal evaluation skipped for {$instrument->symbol}: {$exception->getMessage()}");
            }
        }

        return $summary;
    }

    private function evaluateInstrument(Instrument $instrument): int
    {
        $now = CarbonImmutable::now();
        $to = $now->startOfMinute();
        /** @var Collection<int, Signal> $signals */
        $signals = Signal::open()->where('instrument_id', $instrument->id)->orderBy('published_at')->orderBy('id')->get();
        $cursor = InstrumentFeedCursor::find($instrument->id);

        $from = CarbonImmutable::instance($signals->min('published_at'))->startOfMinute();

        if ($cursor && $cursor->last_candle_at->addMinute()->gt($from)) {
            $from = CarbonImmutable::instance($cursor->last_candle_at)->addMinute();
        }

        $candles = $from->lt($to) ? $this->feed->candles($instrument, $from, $to) : [];
        $closed = 0;

        DB::transaction(function () use ($instrument, $signals, $candles, $now, &$closed) {
            foreach ($candles as $candle) {
                foreach ($signals as $signal) {
                    if ($signal->status->isOpen() && $this->apply($signal, $candle)) {
                        $closed++;
                    }
                }
            }

            foreach ($signals as $signal) {
                if ($signal->status->isOpen() && $this->closeByTime($signal, $now)) {
                    $closed++;
                }
            }

            if ($candles !== []) {
                InstrumentFeedCursor::updateOrCreate(
                    ['instrument_id' => $instrument->id],
                    ['last_candle_at' => end($candles)->time],
                );
            }
        });

        return $closed;
    }

    /**
     * Apply one candle to one open signal. Returns true when the signal closed.
     */
    private function apply(Signal $signal, Candle $candle): bool
    {
        if ($candle->time->lt($signal->published_at)) {
            return false;
        }

        $math = PriceMath::forSignal($signal);
        $spread = $math->spreadPoints();
        [$high, $low] = [$math->points($candle->high), $math->points($candle->low)];
        $isBuy = $signal->direction === SignalDirection::Buy;

        if ($signal->status === SignalStatus::Pending) {
            if ($signal->expires_at && $candle->time->gte($signal->expires_at)) {
                return false;
            }

            if (! $this->touchesEntry($signal, $math, $high, $low, $spread)) {
                return false;
            }

            $signal->update(['status' => SignalStatus::Active, 'triggered_at' => $candle->time]);
            $this->event($signal, 'triggered', $signal->entry_price, $candle->time);
            SignalTriggered::dispatch($signal);
        }

        if ($candle->time->gte($this->timeoutAt($signal))) {
            return false;
        }

        $this->lastClose[$signal->id] = $candle->close;

        // Exit side: buys close on the bid, sells on the ask (bid + spread).
        [$exitHigh, $exitLow] = $isBuy ? [$high, $low] : [$high + $spread, $low + $spread];
        [$tp, $sl] = [$math->points($signal->take_profit), $math->points($signal->stop_loss)];

        $tpHit = $isBuy ? $exitHigh >= $tp : $exitLow <= $tp;
        $slHit = $isBuy ? $exitLow <= $sl : $exitHigh >= $sl;

        if ($tpHit && $slHit) {
            $tpHit = Settings::get('signal.ambiguous_candle_rule') === 'tp_first';
            $slHit = ! $tpHit;
        }

        if ($tpHit) {
            $this->close($signal, SignalStatus::TpHit, $signal->take_profit, $math->pipsBetween($signal->entry_price, $signal->take_profit), (float) $signal->rr, $candle->time);

            return true;
        }

        if ($slHit) {
            $this->close($signal, SignalStatus::SlHit, $signal->stop_loss, -$math->pipsBetween($signal->entry_price, $signal->stop_loss), -1.0, $candle->time);

            return true;
        }

        return false;
    }

    private function touchesEntry(Signal $signal, PriceMath $math, int $high, int $low, int $spread): bool
    {
        $entry = $math->points($signal->entry_price);
        $isLimit = $signal->order_type === OrderType::Limit;

        // Buys fill on the ask (bid + spread), sells on the bid.
        return $signal->direction === SignalDirection::Buy
            ? ($isLimit ? $low + $spread <= $entry : $high + $spread >= $entry)
            : ($isLimit ? $high >= $entry : $low <= $entry);
    }

    /**
     * Expire untouched pending signals and time out long-running active ones.
     */
    private function closeByTime(Signal $signal, CarbonImmutable $now): bool
    {
        if ($signal->status === SignalStatus::Pending) {
            if ($signal->expires_at === null || $now->lt($signal->expires_at)) {
                return false;
            }

            $signal->update(['status' => SignalStatus::Expired, 'closed_at' => $signal->expires_at]);
            $this->event($signal, 'expired', null, CarbonImmutable::instance($signal->expires_at));
            SignalClosed::dispatch($signal);

            return true;
        }

        $timeoutAt = $this->timeoutAt($signal);
        $lastClose = $this->lastClose[$signal->id] ?? null;

        // Without a known price there is nothing to close at; try again next run.
        if ($now->lt($timeoutAt) || $lastClose === null) {
            return false;
        }

        $math = PriceMath::forSignal($signal);
        $exit = $signal->direction === SignalDirection::Buy
            ? $lastClose
            : $math->price($math->points($lastClose) + $math->spreadPoints());
        $result = $math->timeoutResult($signal->direction->value, $signal->entry_price, $signal->stop_loss, $exit);

        $this->close($signal, SignalStatus::ClosedTimeout, $exit, $result['pips'], $result['r'], $timeoutAt);

        return true;
    }

    private function timeoutAt(Signal $signal): CarbonImmutable
    {
        return CarbonImmutable::instance($signal->triggered_at ?? $signal->published_at)
            ->addDays(Settings::get('signal.max_duration_days'));
    }

    private function close(Signal $signal, SignalStatus $status, string $price, float $pips, float $r, CarbonImmutable $at): void
    {
        $signal->update([
            'status' => $status,
            'close_price' => $price,
            'result_pips' => $pips,
            'result_r' => $r,
            'closed_at' => $at,
        ]);

        $this->event($signal, $status->value, $price, $at, ['result_pips' => $pips, 'result_r' => $r]);
        SignalClosed::dispatch($signal);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function event(Signal $signal, string $type, ?string $price, CarbonImmutable $at, ?array $payload = null): void
    {
        $signal->events()->create(['type' => $type, 'price' => $price, 'payload' => $payload, 'occurred_at' => $at]);
    }
}
