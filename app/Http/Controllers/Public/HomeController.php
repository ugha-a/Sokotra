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
            'landing' => [
                'hero_badge' => Settings::get('landing.hero_badge'),
                'hero_title' => Settings::get('landing.hero_title'),
                'hero_subtitle' => Settings::get('landing.hero_subtitle'),
                'hero_cta_primary' => Settings::get('landing.hero_cta_primary'),
                'hero_cta_secondary' => Settings::get('landing.hero_cta_secondary'),
                'features_title' => Settings::get('landing.features_title'),
                'features_subtitle' => Settings::get('landing.features_subtitle'),
                'step1_title' => Settings::get('landing.step1_title'),
                'step1_desc' => Settings::get('landing.step1_desc'),
                'step2_title' => Settings::get('landing.step2_title'),
                'step2_desc' => Settings::get('landing.step2_desc'),
                'step3_title' => Settings::get('landing.step3_title'),
                'step3_desc' => Settings::get('landing.step3_desc'),
                'broker_title' => Settings::get('landing.broker_title'),
                'broker_desc' => Settings::get('landing.broker_desc'),
                'cta_banner_title' => Settings::get('landing.cta_banner_title'),
                'cta_banner_desc' => Settings::get('landing.cta_banner_desc'),
                'cta_banner_button' => Settings::get('landing.cta_banner_button'),
                'faq_q1' => Settings::get('landing.faq_q1'),
                'faq_a1' => Settings::get('landing.faq_a1'),
                'faq_q2' => Settings::get('landing.faq_q2'),
                'faq_a2' => Settings::get('landing.faq_a2'),
                'faq_q3' => Settings::get('landing.faq_q3'),
                'faq_a3' => Settings::get('landing.faq_a3'),
            ],
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
