<?php

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Payment;
use App\Models\TraderProfile;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\SimulatedPaymentGateway;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    app()->instance(PaymentGateway::class, new SimulatedPaymentGateway);
    $this->trader = premiumTrader();
    $this->member = userWithRoles(Role::Member);
    $this->actingAs($this->member)->post(route('subscriptions.store', TraderProfile::where('user_id', $this->trader->id)->value('id')), ['months' => 1, 'accept_risk' => true]);
    $this->payment = Payment::firstOrFail();
});

it('sends the buyer to a local simulated checkout page', function () {
    expect($this->payment->checkout_url)->toBe(route('payments.simulate.show', $this->payment));

    $this->actingAs($this->member)->get($this->payment->checkout_url)
        ->assertInertia(fn (Assert $page) => $page->component('payments/simulate')->where('payment.amount', 150000));
});

it('completes the simulated payment for the buyer only', function () {
    $this->actingAs(userWithRoles(Role::Member))->post(route('payments.simulate.complete', $this->payment), ['outcome' => 'paid'])->assertForbidden();

    $this->actingAs($this->member)
        ->post(route('payments.simulate.complete', $this->payment), ['outcome' => 'paid'])
        ->assertRedirect(route('payments.show', $this->payment));

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Paid);

    $this->actingAs($this->member)->get(route('payments.show', $this->payment))
        ->assertInertia(fn (Assert $page) => $page->component('payments/show')->where('payment.status', 'paid'));
});
