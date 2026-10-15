<?php

namespace App\Actions\Ranking;

use App\Enums\TraderStatus;
use App\Models\Follow;
use App\Models\TraderProfile;
use App\Models\TraderStat;
use App\Support\Settings;
use Illuminate\Support\Collection;

/**
 * Leaderboard rows (docs/logic/scoring-ranking.md → Skor Leaderboard).
 * Tabs: score (weighted, min-max normalised), consistent, featured.
 */
class BuildLeaderboard
{
    public const TABS = ['score', 'consistent', 'featured'];

    public const PERIODS = ['week', 'month', 'all_time'];

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(string $tab, string $period, int $limit = 100): array
    {
        $minSignals = Settings::get($tab === 'consistent' ? 'score.consistent_min_signals' : 'score.leaderboard_min_signals');

        $stats = TraderStat::query()
            ->where('period', $period)
            ->where('closed_signals', '>=', $minSignals)
            ->whereIn('user_id', TraderProfile::query()
                ->select('user_id')
                ->where('status', TraderStatus::Approved)
                ->where('is_flagged', false)
                ->when($tab === 'featured', fn ($query) => $query->where('is_featured', true)))
            ->get();

        if ($stats->isEmpty()) {
            return [];
        }

        $followers = Follow::query()->valid()->whereIn('trader_id', $stats->pluck('user_id'))
            ->selectRaw('trader_id, count(*) as total')->groupBy('trader_id')->pluck('total', 'trader_id');
        $profiles = TraderProfile::query()->with('rank')->whereIn('user_id', $stats->pluck('user_id'))->get()->keyBy('user_id');

        $rows = $stats->map(fn (TraderStat $stat) => [
            'stat' => $stat,
            'followers' => (int) ($followers[$stat->user_id] ?? 0),
            'consistency' => 1 / (1 + (float) $stat->max_drawdown_r),
        ]);

        $rows = $this->score($rows);

        $sorted = $tab === 'consistent'
            ? $rows->sort(fn (array $a, array $b) => [$b['consistency'], (float) $b['stat']->win_rate] <=> [$a['consistency'], (float) $a['stat']->win_rate])
            : $rows->sortByDesc('score');

        return array_values($sorted->take($limit)->values()->map(function (array $row, int $index) use ($profiles) {
            /** @var TraderStat $stat */
            $stat = $row['stat'];
            /** @var TraderProfile $profile */
            $profile = $profiles[$stat->user_id];

            return [
                'position' => $index + 1,
                'trader' => [
                    'name' => $profile->display_name,
                    'slug' => $profile->slug,
                    'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                    'is_featured' => $profile->is_featured,
                ],
                'score' => round($row['score'] * 100, 1),
                'consistency' => round($row['consistency'] * 100, 1),
                'followers' => $row['followers'],
                'stats' => $stat->summary(),
            ];
        })->all());
    }

    /**
     * @param  Collection<int, array{stat: TraderStat, followers: int, consistency: float}>  $rows
     * @return Collection<int, array{stat: TraderStat, followers: int, consistency: float, score: float}>
     */
    private function score(Collection $rows): Collection
    {
        $components = [
            'win_rate' => fn (array $row) => (float) $row['stat']->win_rate,
            'total_r' => fn (array $row) => (float) $row['stat']->total_r,
            'consistency' => fn (array $row) => $row['consistency'],
            'followers' => fn (array $row) => log(1 + $row['followers']),
        ];

        $ranges = [];
        foreach ($components as $name => $value) {
            $values = $rows->map($value);
            $ranges[$name] = [$values->min(), $values->max()];
        }

        return $rows->map(function (array $row) use ($components, $ranges) {
            $score = 0.0;

            foreach ($components as $name => $value) {
                [$min, $max] = $ranges[$name];
                $normalised = $max > $min ? ($value($row) - $min) / ($max - $min) : 1.0;
                $score += Settings::get("score.weight_{$name}") * $normalised;
            }

            return [...$row, 'score' => $score];
        });
    }
}
