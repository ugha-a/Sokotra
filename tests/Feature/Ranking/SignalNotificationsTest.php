<?php

use App\Actions\Signal\EvaluateOpenSignals;
use App\Enums\Role;
use App\Models\Follow;
use App\Models\Instrument;
use App\Models\User;
use App\Notifications\SignalClosedNotification;
use App\Notifications\SignalPublishedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->feed = fakePriceFeed();
    $this->feed->setQuote('EURUSD', '1.08000', '1.08010');
    $this->trader = approvedTrader();
    $this->follower = userWithRoles(Role::Member);
    Follow::create(['follower_id' => $this->follower->id, 'trader_id' => $this->trader->id]);
});

function publishEurusd(User $trader): void
{
    test()->actingAs($trader)->post(route('trader.signals.store'), [
        'instrument_id' => Instrument::where('symbol', 'EURUSD')->value('id'),
        'direction' => 'buy',
        'order_type' => 'market',
        'stop_loss' => '1.07800',
        'take_profit' => '1.08500',
        'visibility' => 'free',
        'analysis' => 'Pantulan dari support harian dengan struktur higher low di H4.',
    ])->assertSessionHasNoErrors();
}

it('notifies followers when a signal is published', function () {
    Notification::fake();
    $stranger = userWithRoles(Role::Member);

    publishEurusd($this->trader);

    Notification::assertSentTo($this->follower, SignalPublishedNotification::class);
    Notification::assertNotSentTo([$this->trader, $stranger], SignalPublishedNotification::class);
});

it('notifies followers when a signal closes', function () {
    Notification::fake();
    $this->travelTo(Carbon::parse('2026-10-16 10:00:00'));
    publishEurusd($this->trader);
    $this->feed->addCandles('EURUSD', [['10:00', '1.08000', '1.08600', '1.08000', '1.08500']]);
    $this->travelTo(Carbon::parse('2026-10-16 10:01:05'));

    app(EvaluateOpenSignals::class)->handle();

    Notification::assertSentTo($this->follower, SignalClosedNotification::class);
});

it('lists notifications, shares the unread count and marks them read', function () {
    publishEurusd($this->trader);

    $this->actingAs($this->follower)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('notifications.unread', 1));

    $this->actingAs($this->follower)
        ->get(route('notifications.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('notifications/index')
            ->has('notifications.data', 1)
            ->where('notifications.data.0.read', false)
            ->where('notifications.data.0.title', fn (string $title) => str_contains($title, 'EURUSD')));

    $this->actingAs($this->follower)->post(route('notifications.read-all'))->assertRedirect();

    expect($this->follower->unreadNotifications()->count())->toBe(0);
});
