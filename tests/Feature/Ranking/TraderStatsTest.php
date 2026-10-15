<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\SignalStatus;
use App\Events\SignalClosed;
use App\Models\Signal;
use App\Models\TraderStat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-16 12:00:00'));
    $this->trader = approvedTrader();
});

it('stores statistics for every period', function () {
    closedSignalsFor($this->trader, [2.0, -1.0, 2.0]);
    closedSignalsFor($this->trader, [3.0], now()->subDays(40));
    closedSignalsFor($this->trader, [-1.0], now()->subDays(200));

    app(RefreshTraderStats::class)->handle($this->trader);

    $stat = fn (string $period) => TraderStat::where('user_id', $this->trader->id)->where('period', $period)->firstOrFail();

    expect($stat('week')->closed_signals)->toBe(3)
        ->and((float) $stat('week')->total_r)->toBe(3.0)
        ->and($stat('month')->closed_signals)->toBe(3)
        ->and($stat('rolling')->closed_signals)->toBe(4)
        ->and($stat('all_time')->closed_signals)->toBe(5)
        ->and((float) $stat('all_time')->win_rate)->toBe(60.0)
        ->and($stat('week')->period_start->toDateString())->toBe('2026-10-12')
        ->and($stat('month')->period_start->toDateString())->toBe('2026-10-01');
});

it('refreshes a trader when one of their signals closes', function () {
    $signal = Signal::factory()->for($this->trader, 'trader')->create();
    $signal->update(['status' => SignalStatus::TpHit, 'result_r' => 2.5, 'result_pips' => 50, 'closed_at' => now()]);

    SignalClosed::dispatch($signal);

    expect(TraderStat::where('user_id', $this->trader->id)->where('period', 'all_time')->value('closed_signals'))->toBe(1);
});

it('reconciles all traders daily and recalculates ranks weekly', function () {
    $commands = collect(app(Schedule::class)->events())->mapWithKeys(fn ($event) => [(string) $event->command => $event]);
    $find = fn (string $name) => $commands->first(fn ($event, $command) => str_contains($command, $name));

    expect($find('stats:refresh')->expression)->toBe('15 0 * * *')
        ->and($find('ranks:recalculate')->expression)->toBe('5 0 * * 1')
        ->and($find('ranks:recalculate')->timezone)->toBe('Asia/Jakarta');
});
