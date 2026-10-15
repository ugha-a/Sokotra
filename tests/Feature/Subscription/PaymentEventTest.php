<?php

use App\Actions\Payments\HandlePaymentEvent;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\TraderProfile;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\SubscriptionActivated;
use App\Services\Payments\PaymentEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->gateway = fakePaymentGateway();
    $this->trader = premiumTrader(150000);
    $this->member = userWithRoles(Role::Member);
    $this->travelTo(Carbon::parse('2026-10-16 10:00:00'));
});

it('activates the subscription and holds the trader income', function () {
    $subscription = paidSubscription($this->member, $this->trader, 3);

    $wallet = Wallet::where('user_id', $this->trader->id)->firstOrFail();
    $income = WalletTransaction::where('reference', "subscription:{$subscription->id}")->firstOrFail();

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->starts_at->toDateTimeString())->toBe('2026-10-16 10:00:00')
        ->and($subscription->ends_at->toDateTimeString())->toBe('2027-01-16 10:00:00')
        ->and($subscription->payment->status)->toBe(PaymentStatus::Paid)
        ->and($wallet->pending_balance)->toBe(360000)
        ->and($income->available_at->toDateString())->toBe('2026-10-23');

    Notification::assertSentTo([$this->member, $this->trader], SubscriptionActivated::class);
});

it('ignores duplicate payment events', function () {
    $subscription = paidSubscription($this->member, $this->trader);
    $payment = $subscription->payment;

    app(HandlePaymentEvent::class)->handle(new PaymentEvent($payment->gateway_reference, PaymentStatus::Paid, []));

    expect(Wallet::where('user_id', $this->trader->id)->value('pending_balance'))->toBe(120000)
        ->and($subscription->fresh()->ends_at->toDateTimeString())->toBe('2026-11-16 10:00:00');
});

it('extends an active subscription from its current end date', function () {
    paidSubscription($this->member, $this->trader);
    $this->travelTo(Carbon::parse('2026-11-01 10:00:00'));

    $renewal = paidSubscription($this->member, $this->trader);

    expect($renewal->starts_at->toDateTimeString())->toBe('2026-11-16 10:00:00')
        ->and($renewal->ends_at->toDateTimeString())->toBe('2026-12-16 10:00:00');
});

it('cancels the subscription when the payment fails', function () {
    $this->actingAs($this->member)->post(route('subscriptions.store', TraderProfile::where('user_id', $this->trader->id)->value('id')), ['months' => 1, 'accept_risk' => true]);
    $payment = Payment::firstOrFail();

    app(HandlePaymentEvent::class)->handle(new PaymentEvent($payment->gateway_reference, PaymentStatus::Expired, []));

    expect($payment->fresh()->status)->toBe(PaymentStatus::Expired)
        ->and($payment->payable->fresh()->status)->toBe(SubscriptionStatus::Cancelled)
        ->and(Wallet::where('user_id', $this->trader->id)->first())->toBeNull();
});

it('accepts signed webhooks and rejects forged ones', function () {
    $this->actingAs($this->member)->post(route('subscriptions.store', TraderProfile::where('user_id', $this->trader->id)->value('id')), ['months' => 1, 'accept_risk' => true]);
    $payment = Payment::firstOrFail();
    auth()->logout();

    $this->postJson(route('webhooks.payments', 'fake'), ['reference' => $payment->gateway_reference, 'status' => 'paid'])
        ->assertForbidden();

    $this->postJson(route('webhooks.payments', 'fake'), ['reference' => $payment->gateway_reference, 'status' => 'paid'], ['X-Fake-Signature' => 'valid'])
        ->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Paid);
});
