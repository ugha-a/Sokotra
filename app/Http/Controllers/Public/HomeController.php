<?php

namespace App\Http\Controllers\Public;

use App\Actions\Ranking\BuildLeaderboard;
use App\Enums\SignalStatus;
use App\Enums\TraderStatus;
use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(BuildLeaderboard $leaderboard): Response
    {
        $scored = [SignalStatus::TpHit, SignalStatus::SlHit, SignalStatus::ClosedTimeout];

        return Inertia::render('public/home', [
            'topTraders' => $leaderboard->handle('score', 'all_time', 3),
            'stats' => [
                'traders' => TraderProfile::where('status', TraderStatus::Approved)->count(),
                'closed_signals' => Signal::whereIn('status', $scored)->count(),
                'losing_signals' => Signal::whereIn('status', $scored)->where('result_r', '<=', 0)->count(),
                'brokers' => Broker::where('is_active', true)->count(),
            ],
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
