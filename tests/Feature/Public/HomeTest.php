<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Models\TraderProfile;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the landing page with top traders and transparent stats', function () {
    $trader = approvedTrader();
    TraderProfile::where('user_id', $trader->id)->update(['display_name' => 'Rina Swing']);
    closedSignalsFor($trader, [...array_fill(0, 18, 2.0), ...array_fill(0, 7, -1.0)]);
    app(RefreshTraderStats::class)->handle($trader);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/home')
            ->has('topTraders', 1)
            ->where('topTraders.0.trader.name', 'Rina Swing')
            ->where('stats.traders', 1)
            ->where('stats.closed_signals', 25)
            ->where('stats.losing_signals', 7)
            ->has('landing.hero_title')
            ->has('landing.features_title')
            ->has('landing.faq_q1')
            ->has('riskDisclaimer'));
});

it('allows admin to edit landing page content and reflects changes on the home page', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'settings' => [
                'landing' => [
                    'hero_title' => 'Judul Baru Landing Page SooTa',
                    'hero_subtitle' => 'Subjudul baru yang dikustomisasi oleh admin.',
                ],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/home')
            ->where('landing.hero_title', 'Judul Baru Landing Page SooTa')
            ->where('landing.hero_subtitle', 'Subjudul baru yang dikustomisasi oleh admin.'));
});
