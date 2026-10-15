<?php

use App\Actions\Subscription\RunSubscriptionLifecycle;
use App\Actions\Wallet\ReleasePendingFunds;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\Signal;
use App\Models\Wallet;
use App\Notifications\InactivePremiumChannels;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Notification::fake();
    fakePaymentGateway();
    $this->travelTo(Carbon::parse('2026-10-16 10:00:00'));
    $this->trader = premiumTrader(150000);
    $this->member = userWithRoles(Role::Member);
    $this->subscription = paidSubscription($this->member, $this->trader);
});

it('reminds subscribers before expiry and expires them afterwards', function () {
    $this->travelTo(Carbon::parse('2026-11-13 09:00:00'));
    app(RunSubscriptionLifecycle::class)->handle();
    Notification::assertSentTo($this->member, SubscriptionExpiring::class);

    $this->travelTo(Carbon::parse('2026-11-16 10:30:00'));
    app(RunSubscriptionLifecycle::class)->handle();

    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Expired);
});

it('alerts admins about premium channels without recent premium signals', function () {
    $admin = adminUser();
    $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));

    app(RunSubscriptionLifecycle::class)->handle();

    Notification::assertSentTo($admin, InactivePremiumChannels::class);
});

it('does not alert when the trader posts premium signals', function () {
    $admin = adminUser();
    Signal::factory()->for($this->trader, 'trader')->create(['visibility' => 'premium', 'published_at' => Carbon::parse('2026-10-25')]);
    $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));

    app(RunSubscriptionLifecycle::class)->handle();

    Notification::assertNotSentTo($admin, InactivePremiumChannels::class);
});

it('runs daily', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains((string) $event->command, 'subscriptions:lifecycle'));

    expect($event?->expression)->toBe('0 6 * * *');
});

it('lets admins refund within the hold period', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.subscriptions.refund', $this->subscription), ['reason' => 'Dobel bayar'])
        ->assertSessionHasNoErrors();

    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Refunded)
        ->and(Wallet::where('user_id', $this->trader->id)->value('pending_balance'))->toBe(0);

    $this->actingAs($this->member)->get(route('signals.index'));
});

it('refuses refunds once the income was released', function () {
    $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
    app(ReleasePendingFunds::class)->handle();

    $this->actingAs(adminUser())
        ->post(route('admin.subscriptions.refund', $this->subscription), ['reason' => 'Terlambat'])
        ->assertSessionHasErrors('subscription');
});

it('lists subscriptions for members and admins', function () {
    $this->actingAs($this->member)->get(route('subscriptions.index'))
        ->assertInertia(fn (Assert $page) => $page->component('member/subscriptions')->has('subscriptions', 1)->where('subscriptions.0.status', 'active'));

    $this->actingAs(adminUser())->get(route('admin.subscriptions.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/subscriptions/index')->has('subscriptions.data', 1));
});
