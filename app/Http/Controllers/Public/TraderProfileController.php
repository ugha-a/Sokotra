<?php

namespace App\Http\Controllers\Public;

use App\Enums\TraderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SignalResource;
use App\Models\Follow;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\TraderStat;
use App\Support\Ranking\TraderStatsCalculator;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TraderProfileController extends Controller
{
    public function show(Request $request, TraderProfile $profile): Response
    {
        abort_unless($profile->status === TraderStatus::Approved, 404);

        $profile->load('rank');
        $viewer = $request->user();
        $stats = TraderStat::where('user_id', $profile->user_id)->get()->keyBy('period');
        $closed = Signal::query()->where('trader_id', $profile->user_id)->whereNotNull('closed_at')
            ->get(['status', 'result_r', 'result_pips', 'rr', 'closed_at']);

        return Inertia::render('public/traders/show', [
            'trader' => [
                'id' => $profile->id,
                'name' => $profile->display_name,
                'slug' => $profile->slug,
                'bio' => $profile->bio,
                'trading_style' => $profile->trading_style->label(),
                'experience_years' => $profile->experience_years,
                'main_instruments' => Instrument::whereIn('id', $profile->main_instruments)->orderBy('symbol')->pluck('symbol')->all(),
                'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                'followers' => Follow::where('trader_id', $profile->user_id)->count(),
                'is_featured' => $profile->is_featured,
                'approved_at' => $profile->approved_at?->toIso8601String(),
            ],
            'stats' => collect(TraderStat::PERIODS)
                ->mapWithKeys(fn (string $period) => [$period => $stats->get($period)?->summary()])
                ->all(),
            'windowDays' => Settings::get('score.window_days'),
            'equityCurve' => TraderStatsCalculator::calculate($closed->all())->equityCurve,
            'signals' => Signal::query()
                ->where('trader_id', $profile->user_id)
                ->with(['instrument', 'trader.traderProfile.rank'])
                ->latest('published_at')->latest('id')
                ->paginate(10)
                ->through(fn (Signal $signal) => (new SignalResource($signal))->resolve($request)),
            'isFollowing' => $viewer !== null
                && Follow::where('follower_id', $viewer->id)->where('trader_id', $profile->user_id)->exists(),
            'isOwnProfile' => $viewer?->id === $profile->user_id,
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
