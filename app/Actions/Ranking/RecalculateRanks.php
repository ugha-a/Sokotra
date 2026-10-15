<?php

namespace App\Actions\Ranking;

use App\Enums\TraderStatus;
use App\Models\ChannelPlan;
use App\Models\Follow;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\TraderRankHistory;
use App\Models\TraderStat;
use App\Notifications\RankChanged;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Weekly rank recalculation from rolling statistics and valid followers
 * (docs/logic/scoring-ranking.md → Rank).
 */
class RecalculateRanks
{
    /**
     * @return int Number of traders whose rank changed.
     */
    public function handle(): int
    {
        /** @var Collection<int, Rank> $ranks */
        $ranks = Rank::query()->where('is_active', true)->orderByDesc('level')->get();
        $changed = 0;

        TraderProfile::query()
            ->where('status', TraderStatus::Approved)
            ->with('user')
            ->chunkById(200, function (Collection $profiles) use ($ranks, &$changed) {
                foreach ($profiles as $profile) {
                    /** @var TraderProfile $profile */
                    if ($this->recalculate($profile, $ranks)) {
                        $changed++;
                    }
                }
            });

        return $changed;
    }

    /**
     * Inputs used for rank thresholds (also shown on the trader dashboard).
     *
     * @return array{closed_signals: int, win_rate: float, total_r: float, valid_followers: int, max_drawdown_r: float}
     */
    public function metrics(TraderProfile $profile): array
    {
        $stat = TraderStat::where('user_id', $profile->user_id)->where('period', 'rolling')->first();

        return [
            'closed_signals' => $stat->closed_signals ?? 0,
            'win_rate' => (float) ($stat->win_rate ?? 0),
            'total_r' => (float) ($stat->total_r ?? 0),
            'valid_followers' => Follow::query()->where('trader_id', $profile->user_id)->valid()->count(),
            'max_drawdown_r' => (float) ($stat->max_drawdown_r ?? 0),
        ];
    }

    /**
     * @param  array{closed_signals: int, win_rate: float, total_r: float, valid_followers: int, max_drawdown_r: float}  $metrics
     * @return list<array{key: string, label: string, required: int|float, current: int|float, met: bool}>
     */
    public function requirements(Rank $rank, array $metrics): array
    {
        $requirements = [
            ['key' => 'closed_signals', 'label' => 'Sinyal selesai', 'required' => $rank->min_closed_signals, 'current' => $metrics['closed_signals']],
            ['key' => 'win_rate', 'label' => 'Win rate (%)', 'required' => (float) $rank->min_win_rate, 'current' => $metrics['win_rate']],
            ['key' => 'total_r', 'label' => 'Total R', 'required' => (float) $rank->min_total_r, 'current' => $metrics['total_r']],
            ['key' => 'valid_followers', 'label' => 'Follower valid', 'required' => $rank->min_valid_followers, 'current' => $metrics['valid_followers']],
        ];

        $result = array_map(fn (array $req) => [...$req, 'met' => $req['current'] >= $req['required']], $requirements);

        if ($rank->max_drawdown_r !== null) {
            $result[] = [
                'key' => 'max_drawdown_r',
                'label' => 'Drawdown maksimal (R)',
                'required' => (float) $rank->max_drawdown_r,
                'current' => $metrics['max_drawdown_r'],
                'met' => $metrics['max_drawdown_r'] <= (float) $rank->max_drawdown_r,
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, Rank>  $ranks  Active ranks, highest level first.
     */
    private function recalculate(TraderProfile $profile, Collection $ranks): bool
    {
        $metrics = $this->metrics($profile);
        $target = $ranks->first(fn (Rank $rank) => collect($this->requirements($rank, $metrics))->every('met'))
            ?? $ranks->last();

        if ($target === null || $target->id === $profile->rank_id) {
            return false;
        }

        $from = $profile->rank_id;

        DB::transaction(function () use ($profile, $target, $from, $metrics) {
            $profile->update(['rank_id' => $target->id]);

            if (! $target->can_sell_premium) {
                // Frozen for new subscribers; existing ones are served until they end.
                ChannelPlan::where('trader_id', $profile->user_id)->update(['is_open' => false]);
            }

            TraderRankHistory::create([
                'user_id' => $profile->user_id,
                'from_rank_id' => $from,
                'to_rank_id' => $target->id,
                'stats' => $metrics,
            ]);
        });

        $profile->user->notify(new RankChanged($profile->fresh(['rank']) ?? $profile, Rank::find($from)));

        return true;
    }
}
