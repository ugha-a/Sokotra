<?php

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\ChannelPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\TraderProfile;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->gateway = fakePaymentGateway();
    $this->trader = premiumTrader(150000);
    $this->profile = TraderProfile::where('user_id', $this->trader->id)->firstOrFail();
    $this->member = userWithRoles(Role::Member);
});

it('shows the checkout page with durations and prices', function () {
    $this->actingAs($this->member)
        ->get(route('subscriptions.create', $this->profile))
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/subscribe')
            ->where('plan.monthly_price', 150000)
            ->where('durations', [1, 3, 6])
            ->has('riskDisclaimer'));
});

it('creates a pending subscription and payment, then redirects to the gateway', function () {
    $response = $this->actingAs($this->member)
        ->post(route('subscriptions.store', $this->profile), ['months' => 3, 'accept_risk' => true]);

    $subscription = Subscription::firstOrFail();
    $payment = Payment::firstOrFail();

    expect($subscription->status)->toBe(SubscriptionStatus::PendingPayment)
        ->and($subscription->price_paid)->toBe(450000)
        ->and($subscription->platform_fee)->toBe(90000)
        ->and($subscription->trader_income)->toBe(360000)
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->amount)->toBe(450000)
        ->and($payment->payable->is($subscription))->toBeTrue()
        ->and($this->member->consents()->where('type', 'risk_disclaimer')->exists())->toBeTrue();

    $response->assertRedirect($payment->checkout_url);
});

it('validates the checkout', function (array $payload, string $field) {
    $this->actingAs($this->member)
        ->post(route('subscriptions.store', $this->profile), ['months' => 1, 'accept_risk' => true, ...$payload])
        ->assertSessionHasErrors($field);

    expect(Subscription::count())->toBe(0);
})->with([
    'unsupported duration' => [['months' => 2], 'months'],
    'risk not accepted' => [['accept_risk' => false], 'accept_risk'],
]);

it('refuses closed channels and subscribing to yourself', function () {
    ChannelPlan::where('trader_id', $this->trader->id)->update(['is_open' => false]);

    $this->actingAs($this->member)
        ->post(route('subscriptions.store', $this->profile), ['months' => 1, 'accept_risk' => true])
        ->assertSessionHasErrors('subscription');

    ChannelPlan::where('trader_id', $this->trader->id)->update(['is_open' => true]);

    $this->actingAs($this->trader)
        ->post(route('subscriptions.store', $this->profile), ['months' => 1, 'accept_risk' => true])
        ->assertSessionHasErrors('subscription');
});
