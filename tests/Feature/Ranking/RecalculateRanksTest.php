<?php

use App\Actions\Ranking\RecalculateRanks;
use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\TraderStatus;
use App\Models\Follow;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\TraderRankHistory;
use App\Models\User;
use App\Notifications\RankChanged;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->trader = approvedTrader();
});

function promoteData(User $trader, int $wins, int $losses, int $followers): void
{
    closedSignalsFor($trader, [...array_fill(0, $wins, 1.5), ...array_fill(0, $losses, -1.0)]);
    followTrader($trader, $followers);
    app(RefreshTraderStats::class)->handle($trader);
}

it('promotes a trader to the highest rank whose thresholds are all met', function () {
    // 30 wins × 1.5R − 20 losses = 25R, win rate 60%, 50 closed, 60 followers → Pro
    promoteData($this->trader, 30, 20, 60);

    app(RecalculateRanks::class)->handle();

    $profile = TraderProfile::where('user_id', $this->trader->id)->firstOrFail();
    $history = TraderRankHistory::latest('id')->firstOrFail();

    expect($profile->rank->slug)->toBe('pro')
        ->and($history->from_rank_id)->toBe(Rank::where('slug', 'newbie')->value('id'))
        ->and($history->to_rank_id)->toBe($profile->rank_id)
        ->and($history->stats['closed_signals'])->toBe(50);

    Notification::assertSentTo($this->trader, RankChanged::class);
});

it('only counts valid followers', function () {
    closedSignalsFor($this->trader, [...array_fill(0, 30, 1.5), ...array_fill(0, 20, -1.0)]);
    followTrader($this->trader, 40);
    followTrader($this->trader, 30, valid: false);
    User::factory()->count(5)->create(['email_verified_at' => now(), 'last_active_at' => now()->subDays(45)])
        ->each(fn ($user) => Follow::create(['follower_id' => $user->id, 'trader_id' => $this->trader->id]));
    app(RefreshTraderStats::class)->handle($this->trader);

    app(RecalculateRanks::class)->handle();

    // 40 valid followers < 50 required for Pro → stays Rookie (needs 10)
    expect(TraderProfile::where('user_id', $this->trader->id)->first()->rank->slug)->toBe('rookie');
});

it('respects the maximum drawdown threshold', function () {
    Rank::where('slug', 'pro')->update(['max_drawdown_r' => 5]);
    // 20 losses in a row at the end → drawdown 20R
    promoteData($this->trader, 30, 20, 60);

    app(RecalculateRanks::class)->handle();

    expect(TraderProfile::where('user_id', $this->trader->id)->first()->rank->slug)->toBe('rookie');
});

it('skips inactive ranks', function () {
    Rank::where('slug', 'pro')->update(['is_active' => false]);
    promoteData($this->trader, 30, 20, 60);

    app(RecalculateRanks::class)->handle();

    expect(TraderProfile::where('user_id', $this->trader->id)->first()->rank->slug)->toBe('rookie');
});

it('does not record history or notify when the rank is unchanged', function () {
    app(RecalculateRanks::class)->handle();

    expect(TraderRankHistory::count())->toBe(0);
    Notification::assertNothingSent();
});

it('leaves suspended traders untouched', function () {
    promoteData($this->trader, 30, 20, 60);
    TraderProfile::where('user_id', $this->trader->id)->update(['status' => TraderStatus::Suspended]);

    app(RecalculateRanks::class)->handle();

    expect(TraderProfile::where('user_id', $this->trader->id)->first()->rank->slug)->toBe('newbie');
});
