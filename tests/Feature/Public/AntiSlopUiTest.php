<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Models\TraderProfile;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $trader = approvedTrader();
    TraderProfile::where('user_id', $trader->id)->update(['display_name' => 'Budi Trader Pro']);
    closedSignalsFor($trader, [...array_fill(0, 16, 2.0), ...array_fill(0, 4, -1.0)]);
    followTrader($trader, 42);
    app(RefreshTraderStats::class)->handle($trader);
});

it('delivers high data density props on landing page without arbitrary decoration', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/home')
            ->has('topTraders', 1)
            ->where('topTraders.0.trader.name', 'Budi Trader Pro')
            ->where('topTraders.0.followers', 42)
            ->where('topTraders.0.stats.win_rate', 80)
            ->where('topTraders.0.stats.closed_signals', 20)
            ->has('stats.traders')
            ->has('stats.closed_signals')
            ->has('stats.losing_signals')
            ->has('stats.brokers')
            ->has('riskDisclaimer')
        );

    // Strict Anti-AI-Slop checks: The source files must not contain decorative slop artifacts
    $homeSource = file_get_contents(resource_path('js/pages/public/home.tsx'));
    $layoutSource = file_get_contents(resource_path('js/layouts/public-layout.tsx'));
    $cssSource = file_get_contents(resource_path('css/app.css'));

    expect(str_contains($homeSource, 'animate-ping'))->toBeFalse('Decorative glowing ping dots are forbidden AI slop');
    expect(str_contains($homeSource, 'text-6xl'))->toBeFalse('Oversized low-density headings are forbidden AI slop');
    expect(str_contains($layoutSource, 'blur-[140px]'))->toBeFalse('Ambient blurred orbs are forbidden AI slop');
    expect(str_contains($cssSource, '.trading-glass'))->toBeFalse('Overused full-page glassmorphism is forbidden AI slop');
    expect(str_contains($cssSource, '.trading-card-glow'))->toBeFalse('Soft glowing shadows are forbidden AI slop');
});

it('delivers structured multi-metric leaderboard data without decorative noise', function () {
    $response = $this->get(route('leaderboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/leaderboard')
            ->where('tab', 'score')
            ->where('period', 'all_time')
            ->has('rows', 1)
            ->where('rows.0.trader.name', 'Budi Trader Pro')
            ->has('rows.0.stats.win_rate')
            ->has('rows.0.stats.total_r')
            ->has('rows.0.stats.max_drawdown_r')
            ->has('rows.0.stats.closed_signals')
            ->has('rows.0.followers')
        );

    $leaderboardSource = file_get_contents(resource_path('js/pages/public/leaderboard.tsx'));
    expect(str_contains($leaderboardSource, 'trading-glass'))->toBeFalse('Table should be clean utilitarian without frosted blur');

    // Domain components must be clean of AI slop
    $tickerSource = file_get_contents(resource_path('js/components/domain/market-ticker-tape.tsx'));
    $signalCardSource = file_get_contents(resource_path('js/components/domain/signal-card.tsx'));
    $calculatorSource = file_get_contents(resource_path('js/components/domain/trading-calculator.tsx'));
    $chartSource = file_get_contents(resource_path('js/components/domain/trading-chart-preview.tsx'));

    expect(str_contains($tickerSource, 'animate-ping'))->toBeFalse('Ticker tape must not use animate-ping');
    expect(str_contains($tickerSource, 'backdrop-blur'))->toBeFalse('Ticker tape must not use backdrop-blur');
    expect(str_contains($signalCardSource, 'animate-ping'))->toBeFalse('Signal card must not use animate-ping');
    expect(str_contains($calculatorSource, 'backdrop-blur'))->toBeFalse('Calculator must not use backdrop-blur');
    expect(str_contains($chartSource, 'animate-ping'))->toBeFalse('Chart preview must not use animate-ping');
    expect(str_contains($chartSource, 'backdrop-blur'))->toBeFalse('Chart preview must not use backdrop-blur');
});
