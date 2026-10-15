<?php

use App\Actions\Signal\EvaluateOpenSignals;
use App\Enums\SignalStatus;
use App\Models\Instrument;
use App\Models\InstrumentFeedCursor;
use App\Models\PlatformSetting;
use App\Models\Signal;
use App\Support\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-16 10:00:00'));
    $this->feed = fakePriceFeed();
});

/**
 * EURUSD signal published at 10:00 (spread snapshot 1 pip = 10 points).
 */
function eurusdSignal(array $overrides = []): Signal
{
    return Signal::factory()->create([
        'instrument_id' => Instrument::where('symbol', 'EURUSD')->value('id'),
        'direction' => 'buy',
        'order_type' => 'market',
        'entry_price' => '1.08000',
        'stop_loss' => '1.07800',
        'take_profit' => '1.08500',
        'rr' => 2.5,
        'sl_pips' => 20,
        'spread_pips_snapshot' => '1.00',
        'status' => SignalStatus::Active,
        'published_at' => now(),
        'triggered_at' => now(),
        ...$overrides,
    ]);
}

function evaluate(): void
{
    app(EvaluateOpenSignals::class)->handle();
}

/** Candle with bid prices: [time, open, high, low, close]. */
function candle(string $time, string $open, string $high, string $low, string $close): array
{
    return [$time, $open, $high, $low, $close];
}

it('closes a buy at take profit', function () {
    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [
        candle('10:00', '1.08000', '1.08200', '1.07950', '1.08150'),
        candle('10:01', '1.08150', '1.08510', '1.08100', '1.08450'),
    ]);
    $this->travelTo(Carbon::parse('2026-10-16 10:02:30'));

    evaluate();
    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::TpHit)
        ->and((float) $signal->result_r)->toBe(2.5)
        ->and((float) $signal->result_pips)->toBe(50.0)
        ->and($signal->close_price)->toBe('1.08500')
        ->and($signal->closed_at->format('H:i'))->toBe('10:01')
        ->and($signal->events()->latest('id')->value('type'))->toBe('tp_hit');
});

it('closes a buy at stop loss', function () {
    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08050', '1.07790', '1.07850')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    evaluate();
    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::SlHit)
        ->and((float) $signal->result_r)->toBe(-1.0)
        ->and((float) $signal->result_pips)->toBe(-20.0);
});

it('counts a candle touching both levels as a loss by default', function () {
    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08600', '1.07700', '1.08000')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    evaluate();

    expect($signal->fresh()->status)->toBe(SignalStatus::SlHit);
});

it('follows the configured ambiguous candle rule', function () {
    PlatformSetting::where('key', 'signal.ambiguous_candle_rule')->firstOrFail()->update(['value' => 'tp_first']);
    Settings::flush();

    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08600', '1.07700', '1.08000')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    evaluate();

    expect($signal->fresh()->status)->toBe(SignalStatus::TpHit);
});

it('exits sells on the ask side using the spread', function () {
    $signal = eurusdSignal([
        'direction' => 'sell',
        'entry_price' => '1.08000',
        'stop_loss' => '1.08200',
        'take_profit' => '1.07500',
    ]);
    // Bid low 1.07495 touches TP, but ask (bid + 1 pip) only reaches 1.07505.
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08050', '1.07495', '1.07600')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    evaluate();
    expect($signal->fresh()->status)->toBe(SignalStatus::Active);

    $this->feed->addCandles('EURUSD', [candle('10:01', '1.07600', '1.07650', '1.07490', '1.07550')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:02:05'));

    evaluate();
    expect($signal->fresh()->status)->toBe(SignalStatus::TpHit);
});

it('ignores prices from before the signal was published', function () {
    $signal = eurusdSignal(['published_at' => Carbon::parse('2026-10-16 10:00:30'), 'triggered_at' => Carbon::parse('2026-10-16 10:00:30')]);
    $this->feed->addCandles('EURUSD', [
        candle('10:00', '1.08000', '1.08600', '1.08000', '1.08100'),
        candle('10:01', '1.08100', '1.08200', '1.08050', '1.08150'),
    ]);
    $this->travelTo(Carbon::parse('2026-10-16 10:02:05'));

    evaluate();

    expect($signal->fresh()->status)->toBe(SignalStatus::Active);
});

it('triggers pending limit orders on the ask side and then evaluates them', function () {
    $signal = eurusdSignal([
        'order_type' => 'limit',
        'status' => SignalStatus::Pending,
        'entry_price' => '1.07900',
        'stop_loss' => '1.07700',
        'take_profit' => '1.08400',
        'triggered_at' => null,
        'expires_at' => now()->addHours(24),
    ]);
    $this->feed->addCandles('EURUSD', [
        // ask low = 1.07900 → buy limit fills
        candle('10:00', '1.08000', '1.08000', '1.07890', '1.07950'),
        candle('10:01', '1.07950', '1.08410', '1.07940', '1.08380'),
    ]);
    $this->travelTo(Carbon::parse('2026-10-16 10:02:05'));

    evaluate();
    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::TpHit)
        ->and($signal->triggered_at->format('H:i'))->toBe('10:00')
        ->and($signal->events()->pluck('type')->all())->toContain('triggered', 'tp_hit');
});

it('expires pending orders that never trigger', function () {
    $signal = eurusdSignal([
        'order_type' => 'limit',
        'status' => SignalStatus::Pending,
        'entry_price' => '1.07000',
        'stop_loss' => '1.06800',
        'take_profit' => '1.07500',
        'triggered_at' => null,
        'expires_at' => now()->addHours(24),
    ]);
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08100', '1.07900', '1.08000')]);
    $this->travelTo(Carbon::parse('2026-10-17 10:00:30'));

    evaluate();
    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::Expired)
        ->and($signal->closed_at->equalTo($signal->expires_at))->toBeTrue()
        ->and($signal->result_r)->toBeNull();
});

it('closes active signals that exceed the maximum duration at the last price', function () {
    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08100', '1.07950', '1.08000')]);
    $this->feed->addCandles('EURUSD', [candle('2026-10-23 09:59', '1.08080', '1.08110', '1.08070', '1.08100')]);
    $this->travelTo(Carbon::parse('2026-10-23 10:00:30'));

    evaluate();
    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::ClosedTimeout)
        ->and($signal->close_price)->toBe('1.08100')
        ->and((float) $signal->result_pips)->toBe(10.0)
        ->and((float) $signal->result_r)->toBe(0.5);
});

it('does not guess when the price feed fails', function () {
    $signal = eurusdSignal();
    $this->feed->failFor('EURUSD');
    $this->travelTo(Carbon::parse('2026-10-16 10:05:00'));

    evaluate();

    expect($signal->fresh()->status)->toBe(SignalStatus::Active)
        ->and(InstrumentFeedCursor::count())->toBe(0);

    $this->feed->recover('EURUSD');
    $this->feed->addCandles('EURUSD', [candle('10:03', '1.08000', '1.08510', '1.08000', '1.08500')]);

    evaluate();

    expect($signal->fresh()->status)->toBe(SignalStatus::TpHit);
});

it('processes each candle only once', function () {
    $signal = eurusdSignal();
    $this->feed->addCandles('EURUSD', [candle('10:00', '1.08000', '1.08100', '1.07950', '1.08050')]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    evaluate();
    $this->travelTo(Carbon::parse('2026-10-16 10:02:05'));
    evaluate();

    expect(InstrumentFeedCursor::firstOrFail()->last_candle_at->format('H:i'))->toBe('10:00')
        ->and($this->feed->requests('EURUSD')[1]['from']->format('H:i'))->toBe('10:01')
        ->and($signal->fresh()->events()->count())->toBe(0);
});

it('is scheduled every minute', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'signals:evaluate'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('* * * * *');
});
