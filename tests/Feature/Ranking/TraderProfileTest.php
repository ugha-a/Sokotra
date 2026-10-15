<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\Role;
use App\Models\Follow;
use App\Models\Signal;
use App\Models\TraderProfile;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->trader = approvedTrader('elite');
    $this->profile = TraderProfile::where('user_id', $this->trader->id)->firstOrFail();
    closedSignalsFor($this->trader, [2.0, -1.0, 1.5]);
    app(RefreshTraderStats::class)->handle($this->trader);
});

it('shows a public trader profile with stats, equity curve and signals', function () {
    Signal::factory()->for($this->trader, 'trader')->create(['visibility' => 'premium']);

    $this->get(route('traders.show', $this->profile->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/traders/show')
            ->where('trader.name', $this->profile->display_name)
            ->where('trader.rank.name', 'Elite')
            ->where('trader.followers', 0)
            ->has('stats.all_time')
            ->where('stats.all_time.closed_signals', 3)
            ->has('equityCurve', 3)
            ->has('signals.data', 4)
            ->where('signals.data.0.locked', true)
            ->where('isFollowing', false)
            ->where('isOwnProfile', false));
});

it('knows when the viewer follows the trader', function () {
    $member = userWithRoles(Role::Member);
    Follow::create(['follower_id' => $member->id, 'trader_id' => $this->trader->id]);

    $this->actingAs($member)
        ->get(route('traders.show', $this->profile->slug))
        ->assertInertia(fn (Assert $page) => $page->where('isFollowing', true)->where('trader.followers', 1));
});

it('hides profiles of traders who are not approved', function () {
    $applicant = TraderProfile::factory()->create();

    $this->get(route('traders.show', $applicant->slug))->assertNotFound();
});
