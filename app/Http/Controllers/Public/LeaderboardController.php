<?php

namespace App\Http\Controllers\Public;

use App\Actions\Ranking\BuildLeaderboard;
use App\Http\Controllers\Controller;
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

        return Inertia::render('public/leaderboard', [
            'tab' => $tab,
            'period' => $period,
            'rows' => $leaderboard->handle($tab, $period),
            'minSignals' => Settings::get($tab === 'consistent' ? 'score.consistent_min_signals' : 'score.leaderboard_min_signals'),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
