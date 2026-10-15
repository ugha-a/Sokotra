<?php

namespace App\Http\Controllers\Public;

use App\Actions\Ranking\BuildLeaderboard;
use App\Http\Controllers\Controller;
use App\Models\TraderStat;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request, BuildLeaderboard $leaderboard): Response
    {
        $validated = $request->validate([
            'tab' => ['nullable', Rule::in(BuildLeaderboard::TABS)],
            'period' => ['nullable', Rule::in(BuildLeaderboard::PERIODS)],
        ]);

        $tab = $validated['tab'] ?? 'score';
        $period = $validated['period'] ?? 'all_time';

        $rows = $leaderboard->handle($tab, $period);
        $minSignals = (int) Settings::get($tab === 'consistent' ? 'score.consistent_min_signals' : 'score.leaderboard_min_signals');

        $myStanding = null;
        if ($user = $request->user()) {
            $profile = $user->traderProfile;
            if ($profile) {
                $stat = TraderStat::where('user_id', $user->id)->where('period', $period)->first();
                $rankedRow = collect($rows)->firstWhere('trader.id', $user->id);
                $myStanding = [
                    'is_trader' => true,
                    'is_ranked' => (bool) $rankedRow,
                    'position' => $rankedRow['position'] ?? null,
                    'score' => $rankedRow['score'] ?? null,
                    'consistency' => $rankedRow['consistency'] ?? null,
                    'closed_signals' => $stat?->closed_signals ?? 0,
                    'min_signals' => $minSignals,
                    'win_rate' => $stat ? (float) $stat->win_rate : 0.0,
                    'total_r' => $stat ? (float) $stat->total_r : 0.0,
                    'max_drawdown_r' => $stat ? (float) $stat->max_drawdown_r : 0.0,
                    'profile_slug' => $profile->slug,
                    'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                ];
            } else {
                $myStanding = [
                    'is_trader' => false,
                ];
            }
        }

        return Inertia::render('public/leaderboard', [
            'tab' => $tab,
            'period' => $period,
            'rows' => $rows,
            'minSignals' => $minSignals,
            'myStanding' => $myStanding,
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
