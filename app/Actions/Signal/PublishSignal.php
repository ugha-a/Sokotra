<?php

namespace App\Actions\Signal;

use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Enums\SignalStatus;
use App\Enums\SignalVisibility;
use App\Enums\TraderStatus;
use App\Events\SignalTriggered;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\User;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\PriceFeedUnavailable;
use App\Services\PriceFeed\Quote;
use App\Support\Settings;
use App\Support\Signal\PriceMath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Validates levels against the live price and publishes an immutable signal
 * (docs/logic/signal-lifecycle.md → Validasi Publish).
 */
class PublishSignal
{
    public const DISK = 'local';

    public function __construct(private PriceFeed $feed) {}

    /**
     * Why this user cannot publish right now, or null when they can.
     */
    public function blockReason(User $trader): ?string
    {
        $profile = TraderProfile::where('user_id', $trader->id)->first();

        return match ($profile?->status) {
            TraderStatus::Approved => null,
            TraderStatus::Suspended => 'Akun trader kamu sedang ditangguhkan, jadi belum bisa memposting sinyal.',
            default => 'Akun trader kamu belum aktif.',
        };
    }

    /**
     * @param  array<string, mixed>  $data  Validated by PublishSignalRequest.
     */
    public function handle(User $trader, array $data, ?UploadedFile $chart, ?string $ip, ?string $userAgent): Signal
    {
        if ($reason = $this->blockReason($trader)) {
            throw ValidationException::withMessages(['signal' => $reason]);
        }

        $this->ensureCapacity($trader);

        $instrument = Instrument::query()->whereKey((int) $data['instrument_id'])->firstOrFail();
        $direction = SignalDirection::from($data['direction']);
        $orderType = OrderType::from($data['order_type']);
        $visibility = SignalVisibility::from($data['visibility']);

        $this->ensureVisibilityAllowed($trader, $visibility);

        $quote = $this->quote($instrument);
        $math = PriceMath::forInstrument($instrument);
        $entry = $orderType === OrderType::Market
            ? ($direction === SignalDirection::Buy ? $quote->ask : $quote->bid)
            : $math->price($math->points($data['entry_price']));
        $stopLoss = $math->price($math->points($data['stop_loss']));
        $takeProfit = $math->price($math->points($data['take_profit']));

        $this->validateLevels($math, $instrument, $direction, $orderType, $quote, $entry, $stopLoss, $takeProfit);

        $chartPath = $chart?->store("signals/{$trader->id}", self::DISK) ?: null;

        try {
            return DB::transaction(fn () => $this->create(
                $trader, $instrument, $direction, $orderType, $visibility, $math,
                $entry, $stopLoss, $takeProfit, (string) $data['analysis'], $chartPath, $quote, $ip, $userAgent,
            ));
        } catch (Throwable $exception) {
            if ($chartPath) {
                Storage::disk(self::DISK)->delete($chartPath);
            }

            throw $exception;
        }
    }

    private function ensureCapacity(User $trader): void
    {
        $max = Settings::get('signal.max_active_per_trader');

        if (Signal::open()->where('trader_id', $trader->id)->count() >= $max) {
            throw ValidationException::withMessages([
                'signal' => "Kamu sudah punya {$max} sinyal terbuka. Tunggu salah satunya selesai atau batalkan sinyal pending.",
            ]);
        }
    }

    private function ensureVisibilityAllowed(User $trader, SignalVisibility $visibility): void
    {
        if ($visibility !== SignalVisibility::Premium) {
            return;
        }

        $canSellPremium = TraderProfile::where('user_id', $trader->id)->first()?->rank->can_sell_premium ?? false;

        if (! $canSellPremium) {
            throw ValidationException::withMessages(['visibility' => 'Rank kamu belum bisa memposting sinyal premium.']);
        }
    }

    private function quote(Instrument $instrument): Quote
    {
        try {
            return $this->feed->quote($instrument);
        } catch (PriceFeedUnavailable) {
            throw ValidationException::withMessages([
                'signal' => "Harga live {$instrument->symbol} sedang tidak tersedia. Coba lagi sebentar.",
            ]);
        }
    }

    private function validateLevels(
        PriceMath $math,
        Instrument $instrument,
        SignalDirection $direction,
        OrderType $orderType,
        Quote $quote,
        string $entry,
        string $stopLoss,
        string $takeProfit,
    ): void {
        $errors = [];
        [$e, $sl, $tp] = [$math->points($entry), $math->points($stopLoss), $math->points($takeProfit)];
        $isBuy = $direction === SignalDirection::Buy;
        $entryNote = $orderType === OrderType::Market ? " (harga live saat ini {$entry})" : '';

        if ($orderType !== OrderType::Market) {
            $market = $math->points($isBuy ? $quote->ask : $quote->bid);
            $belowMarket = ($isBuy && $orderType === OrderType::Limit) || (! $isBuy && $orderType === OrderType::Stop);

            if ($belowMarket && $e >= $market) {
                $errors['entry_price'] = "Entry {$orderType->label()} harus di bawah harga saat ini ({$math->price($market)}).";
            } elseif (! $belowMarket && $e <= $market) {
                $errors['entry_price'] = "Entry {$orderType->label()} harus di atas harga saat ini ({$math->price($market)}).";
            }
        }

        if ($isBuy ? $sl >= $e : $sl <= $e) {
            $errors['stop_loss'] = ($isBuy ? 'Stop Loss buy harus di bawah entry' : 'Stop Loss sell harus di atas entry').$entryNote.'.';
        }

        if ($isBuy ? $tp <= $e : $tp >= $e) {
            $errors['take_profit'] = ($isBuy ? 'Take Profit buy harus di atas entry' : 'Take Profit sell harus di bawah entry').$entryNote.'.';
        }

        if ($errors === []) {
            $slPips = $math->pipsBetween($entry, $stopLoss);
            $rr = $math->riskReward($entry, $stopLoss, $takeProfit);
            $minRr = Settings::get('signal.min_rr');

            if ($slPips < (float) $instrument->min_sl_pips) {
                $errors['stop_loss'] = "Jarak Stop Loss minimal {$instrument->min_sl_pips} pips (saat ini {$slPips} pips).";
            }

            if ($rr < $minRr) {
                $errors['take_profit'] = 'Risk Reward minimal 1:'.number_format($minRr, 2, ',', '.').' (saat ini 1:'.number_format($rr, 2, ',', '.').'). Jauhkan TP atau dekatkan SL.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function create(
        User $trader,
        Instrument $instrument,
        SignalDirection $direction,
        OrderType $orderType,
        SignalVisibility $visibility,
        PriceMath $math,
        string $entry,
        string $stopLoss,
        string $takeProfit,
        string $analysis,
        ?string $chartPath,
        Quote $quote,
        ?string $ip,
        ?string $userAgent,
    ): Signal {
        $isMarket = $orderType === OrderType::Market;

        $signal = Signal::create([
            'trader_id' => $trader->id,
            'instrument_id' => $instrument->id,
            'pip_size_snapshot' => $instrument->pip_size,
            'spread_pips_snapshot' => $instrument->spread_pips,
            'digits_snapshot' => $instrument->digits,
            'direction' => $direction,
            'order_type' => $orderType,
            'entry_price' => $entry,
            'stop_loss' => $stopLoss,
            'take_profit' => $takeProfit,
            'rr' => $math->riskReward($entry, $stopLoss, $takeProfit),
            'sl_pips' => $math->pipsBetween($entry, $stopLoss),
            'visibility' => $visibility,
            'analysis' => $analysis,
            'chart_image_path' => $chartPath,
            'status' => $isMarket ? SignalStatus::Active : SignalStatus::Pending,
            'published_at' => now(),
            'triggered_at' => $isMarket ? now() : null,
            'expires_at' => $isMarket ? null : now()->addHours(Settings::get('signal.pending_expiry_hours')),
            'publish_ip' => $ip,
            'publish_device_hash' => $userAgent ? hash('sha256', $userAgent) : null,
        ]);

        $signal->events()->create([
            'type' => 'published',
            'price' => $entry,
            'payload' => ['bid' => $quote->bid, 'ask' => $quote->ask, 'quoted_at' => $quote->time->toIso8601String()],
            'occurred_at' => now(),
        ]);

        if ($isMarket) {
            $signal->events()->create(['type' => 'triggered', 'price' => $entry, 'occurred_at' => now()]);
            SignalTriggered::dispatch($signal);
        }

        return $signal;
    }
}
