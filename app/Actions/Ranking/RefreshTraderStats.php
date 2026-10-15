<?php

namespace App\Actions\Ranking;

use App\Enums\SignalStatus;
use App\Models\Signal;
use App\Models\TraderStat;
use App\Models\User;
use App\Support\BusinessCalendar;
use App\Support\Ranking\TraderStats;
use App\Support\Ranking\TraderStatsCalculator;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Recomputes trader_stats for every period from the trader's closed signals.
 */
class RefreshTraderStats
{
    public function handle(User $trader): TraderStats
    {
        /** @var Collection<int, Signal> $signals */
        $signals = Signal::query()
            ->where('trader_id', $trader->id)
            ->whereNotIn('status', SignalStatus::open())
            ->whereNotNull('closed_at')
            ->get(['id', 'status', 'result_r', 'result_pips', 'rr', 'closed_at']);

        $countExpired = (bool) Settings::get('signal.count_expired_in_streak');
        $allTime = null;

        foreach ($this->periods() as $period => $start) {
            $inPeriod = $start === null ? $signals : $signals->filter(fn (Signal $signal) => $signal->closed_at->gte($start));
            $stats = TraderStatsCalculator::calculate($inPeriod->all(), $countExpired);

            TraderStat::updateOrCreate(
                ['user_id' => $trader->id, 'period' => $period],
                [
                    ...$stats->toColumns(),
                    'period_start' => $start?->setTimezone(BusinessCalendar::timezone())->toDateString(),
                    'computed_at' => now(),
                ],
            );

            $allTime ??= $period === 'all_time' ? $stats : null;
        }

        /** @var TraderStats $allTime */
        return $allTime;
    }

    /**
     * @return array<string, CarbonImmutable|null>
     */
    private function periods(): array
    {
        return [
            'week' => BusinessCalendar::startOfWeek()->utc(),
            'month' => BusinessCalendar::startOfMonth()->utc(),
            'rolling' => CarbonImmutable::now()->subDays(Settings::get('score.window_days')),
            'all_time' => null,
        ];
    }
}
