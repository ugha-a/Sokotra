<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\Role;
use App\Models\Follow;
use App\Models\PayrollRule;
use App\Models\Rank;
use App\Models\Signal;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-16 12:00:00'));
});

it('shows traders their stats, streak progress and next rank requirements', function () {
    $trader = approvedTrader();
    PayrollRule::where('rank_id', Rank::where('slug', 'newbie')->value('id'))->update(['required_streak' => 10]);
    closedSignalsFor($trader, [-1.0, 2.0, 2.0, 2.0]);
    app(RefreshTraderStats::class)->handle($trader);

    $this->actingAs($trader)
        ->get(route('trader.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('trader/dashboard')
            ->where('stats.month.closed_signals', 4)
            ->where('streak.current', 3)
            ->where('streak.required', 10)
            ->where('rank.name', 'Newbie')
            ->where('nextRank.name', 'Rookie')
            ->where('nextRank.requirements.0.key', 'closed_signals')
            ->where('nextRank.requirements.0.required', 20)
            ->where('nextRank.requirements.0.current', 4)
            ->where('nextRank.requirements.0.met', false));
});

it('shows members the latest signals from traders they follow', function () {
    $member = userWithRoles(Role::Member);
    $followed = approvedTrader();
    $other = approvedTrader();
    Follow::create(['follower_id' => $member->id, 'trader_id' => $followed->id]);
    Signal::factory()->count(2)->for($followed, 'trader')->create();
    Signal::factory()->for($other, 'trader')->create();

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('followingCount', 1)
            ->has('feed', 2));
});

it('lists followed traders', function () {
    $member = userWithRoles(Role::Member);
    $trader = approvedTrader();
    Follow::create(['follower_id' => $member->id, 'trader_id' => $trader->id]);

    $this->actingAs($member)
        ->get(route('following.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/following')
            ->has('traders', 1));
});

it('sends trader-role users without a profile to the application form', function () {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->get(route('trader.dashboard'))
        ->assertRedirect(route('become-trader.show'));
});
