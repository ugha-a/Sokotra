<?php

use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\Role;
use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-16 12:00:00'));
    $this->member = userWithRoles(Role::Member);
});

function rankedTrader(string $name, array $results, int $followers = 0): User
{
    $trader = approvedTrader();
    TraderProfile::where('user_id', $trader->id)->update(['display_name' => $name]);
    closedSignalsFor($trader, $results);
    followTrader($trader, $followers);
    app(RefreshTraderStats::class)->handle($trader);

    return $trader;
}

it('requires authentication to view leaderboard', function () {
    $this->get(route('leaderboard'))->assertRedirect(route('login'));
});

it('ranks traders by weighted score for authenticated members', function () {
    rankedTrader('Sedang', [...array_fill(0, 15, 1.5), ...array_fill(0, 10, -1.0)], 5);
    rankedTrader('Terbaik', [...array_fill(0, 20, 2.0), ...array_fill(0, 5, -1.0)], 20);
    rankedTrader('Sedikit Sinyal', [2.0, 2.0, 2.0], 100);

    $this->actingAs($this->member)
        ->get(route('leaderboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/leaderboard')
            ->where('tab', 'score')
            ->where('period', 'all_time')
            ->has('rows', 2)
            ->where('rows.0.trader.name', 'Terbaik')
            ->where('rows.0.position', 1)
            ->where('rows.1.trader.name', 'Sedang')
            ->where('minSignals', 20));
});

it('excludes suspended and flagged traders', function () {
    $suspended = rankedTrader('Ditangguhkan', array_fill(0, 25, 2.0));
    $flagged = rankedTrader('Ditandai', array_fill(0, 25, 2.0));
    TraderProfile::where('user_id', $suspended->id)->update(['status' => TraderStatus::Suspended]);
    TraderProfile::where('user_id', $flagged->id)->update(['is_flagged' => true]);

    $this->actingAs($this->member)
        ->get(route('leaderboard'))
        ->assertInertia(fn (Assert $page) => $page->has('rows', 0));
});

it('lists the most consistent traders with enough signals', function () {
    rankedTrader('Stabil', [...array_fill(0, 40, 1.0), ...array_fill(0, 15, -0.5)]);
    rankedTrader('Kurang Sinyal', array_fill(0, 30, 1.0));

    $this->actingAs($this->member)
        ->get(route('leaderboard', ['tab' => 'consistent']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tab', 'consistent')
            ->has('rows', 1)
            ->where('rows.0.trader.name', 'Stabil'));
});

it('shows featured traders', function () {
    $featured = rankedTrader('Pilihan', array_fill(0, 25, 1.0));
    rankedTrader('Biasa', array_fill(0, 25, 1.0));
    TraderProfile::where('user_id', $featured->id)->update(['is_featured' => true]);

    $this->actingAs($this->member)
        ->get(route('leaderboard', ['tab' => 'featured']))
        ->assertInertia(fn (Assert $page) => $page->has('rows', 1)->where('rows.0.trader.name', 'Pilihan'));
});

it('switches the period', function () {
    rankedTrader('Minggu Ini', array_fill(0, 25, 1.0));

    $this->actingAs($this->member)
        ->get(route('leaderboard', ['period' => 'week']))
        ->assertInertia(fn (Assert $page) => $page->where('period', 'week')->has('rows', 1));
});

it('delivers trader standing when logged in as ranked trader', function () {
    $trader = rankedTrader('Trader Saya', array_fill(0, 25, 2.0), 10);

    $this->actingAs($trader)
        ->get(route('leaderboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/leaderboard')
            ->where('myStanding.is_trader', true)
            ->where('myStanding.is_ranked', true)
            ->where('myStanding.position', 1)
            ->has('myStanding.score')
            ->where('myStanding.closed_signals', 25)
            ->where('rows.0.trader.id', $trader->id)
        );
});

it('delivers trader standing with progress when trader is not yet qualified', function () {
    $trader = rankedTrader('Trader Pemula', array_fill(0, 5, 2.0));

    $this->actingAs($trader)
        ->get(route('leaderboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/leaderboard')
            ->where('myStanding.is_trader', true)
            ->where('myStanding.is_ranked', false)
            ->where('myStanding.position', null)
            ->where('myStanding.closed_signals', 5)
            ->where('myStanding.min_signals', 20)
        );
});
