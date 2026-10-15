<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Resources\SignalResource;
use App\Models\Follow;
use App\Models\Signal;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $followed = Follow::where('follower_id', $request->user()?->id)->pluck('trader_id');

        return Inertia::render('dashboard', [
            'followingCount' => $followed->count(),
            'feed' => Signal::query()
                ->whereIn('trader_id', $followed)
                ->with(['instrument', 'trader.traderProfile.rank'])
                ->latest('published_at')->latest('id')
                ->limit(10)
                ->get()
                ->map(fn (Signal $signal) => (new SignalResource($signal))->resolve($request))
                ->all(),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
