<?php

namespace App\Http\Controllers\Trader;

use App\Actions\Ranking\RecalculateRanks;
use App\Http\Controllers\Controller;
use App\Models\PayrollRule;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\TraderStat;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RecalculateRanks $ranks): Response|RedirectResponse
    {
        $profile = TraderProfile::with('rank')->where('user_id', $request->user()?->id)->first();

        if ($profile === null) {
            return to_route('become-trader.show');
        }

        $stats = TraderStat::where('user_id', $profile->user_id)->get()->keyBy('period');
        $rule = $profile->rank_id ? PayrollRule::where('rank_id', $profile->rank_id)->first() : null;
        $next = Rank::query()->where('is_active', true)->where('level', '>', $profile->rank->level ?? 0)->orderBy('level')->first();
        $metrics = $ranks->metrics($profile);

        return Inertia::render('trader/dashboard', [
            'profile' => ['name' => $profile->display_name, 'slug' => $profile->slug, 'status' => $profile->status->value, 'status_label' => $profile->status->label()],
            'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
            'stats' => collect(TraderStat::PERIODS)->mapWithKeys(fn (string $period) => [$period => $stats->get($period)?->summary()])->all(),
            'windowDays' => Settings::get('score.window_days'),
            'streak' => [
                'current' => $stats->get('all_time')->current_streak ?? 0,
                'best_this_month' => $stats->get('month')->best_streak ?? 0,
                'required' => $rule->required_streak ?? null,
            ],
            'nextRank' => $next ? [
                'name' => $next->name,
                'color' => $next->color,
                'requirements' => $ranks->requirements($next, $metrics),
            ] : null,
            'validFollowers' => $metrics['valid_followers'],
        ]);
    }
}
