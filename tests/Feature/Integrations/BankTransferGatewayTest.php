<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\TraderProfile;
use App\Services\Payments\BankTransferPaymentGateway;
use App\Services\Payments\InvalidWebhookSignature;
use App\Services\Payments\PaymentGateway;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->gateway = new BankTransferPaymentGateway;
    app()->instance(PaymentGateway::class, $this->gateway);
});

it('creates a bank transfer checkout redirecting to payments.show', function () {
    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);

    $response = $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();

    expect($payment->gateway)->toBe('bank_transfer')
        ->and($payment->gateway_reference)->toStartWith("TRF-{$payment->id}-")
        ->and($payment->checkout_url)->toBe(route('payments.show', $payment));

    $response->assertRedirect(route('payments.show', $payment));
});

it('shows bank destination details on payments.show', function () {
    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);

    $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();

    $this->actingAs($member)
        ->get(route('payments.show', $payment))
        ->assertInertia(fn (Assert $page) => $page
            ->component('payments/show')
            ->where('payment.id', $payment->id)
            ->where('payment.gateway', 'bank_transfer')
            ->where('payment.status', 'pending')
            ->where('payment.bank_destination.bank_name', Settings::get('payment.bank_name'))
            ->where('payment.bank_destination.account_number', Settings::get('payment.account_number'))
            ->where('payment.bank_destination.account_holder', Settings::get('payment.account_holder'))
            ->where('payment.bank_destination.instructions', Settings::get('payment.instructions'))
            ->where('payment.proof.has_proof', false));
});

it('allows the buyer to submit transfer proof', function () {
    Storage::fake('local');

    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);

    $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();
    $file = UploadedFile::fake()->image('bukti-transfer.jpg');

    $this->actingAs($member)
        ->post(route('payments.proof', $payment), [
            'proof' => $file,
            'sender_name' => 'Budi Santoso',
            'sender_bank' => 'BCA',
        ])
        ->assertRedirect(route('payments.show', $payment));

    $payment->refresh();
    expect($payment->raw_payload['sender_name'])->toBe('Budi Santoso')
        ->and($payment->raw_payload['sender_bank'])->toBe('BCA')
        ->and($payment->raw_payload['proof_path'])->not->toBeNull();

    Storage::disk('local')->assertExists($payment->raw_payload['proof_path']);
});

it('allows admin to view transfer proof', function () {
    Storage::fake('local');

    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);
    $admin = adminUser();

    $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();
    $file = UploadedFile::fake()->image('bukti.jpg');

    $this->actingAs($member)->post(route('payments.proof', $payment), [
        'proof' => $file,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.payments.proof', $payment))
        ->assertOk();

    // Regular member cannot view
    $this->actingAs($member)
        ->get(route('admin.payments.proof', $payment))
        ->assertForbidden();
});

it('allows admin to confirm payment and activates subscription', function () {
    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);
    $admin = adminUser();

    $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();
    $subscription = Subscription::firstOrFail();

    expect($subscription->status)->toBe(SubscriptionStatus::PendingPayment);

    $this->actingAs($admin)
        ->post(route('admin.payments.confirm', $payment))
        ->assertRedirect();

    $payment->refresh();
    $subscription->refresh();

    expect($payment->status)->toBe(PaymentStatus::Paid)
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->starts_at)->not->toBeNull()
        ->and($subscription->ends_at)->not->toBeNull();
});

it('allows admin to confirm order payment and marks order paid', function () {
    $seller = sellerTrader();
    $product = publishedProduct($seller, ['price' => 200000]);
    $buyer = userWithRoles(Role::Member);
    $admin = adminUser();

    $this->actingAs($buyer)->post(
        route('orders.store', $product),
        ['accept_risk' => true],
    );

    $payment = Payment::firstOrFail();
    $order = Order::firstOrFail();

    expect($order->status)->toBe(OrderStatus::PendingPayment);

    $this->actingAs($admin)
        ->post(route('admin.payments.confirm', $payment))
        ->assertRedirect();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(PaymentStatus::Paid)
        ->and($order->status)->toBe(OrderStatus::Paid)
        ->and($order->license_key)->toStartWith('SKT-');
});

it('allows admin to reject payment with a reason', function () {
    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);
    $admin = adminUser();

    $this->actingAs($member)->post(
        route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')),
        ['months' => 1, 'accept_risk' => true],
    );

    $payment = Payment::firstOrFail();
    $subscription = Subscription::firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.payments.reject', $payment), ['reason' => 'Dana tidak masuk'])
        ->assertRedirect();

    $payment->refresh();
    $subscription->refresh();

    expect($payment->status)->toBe(PaymentStatus::Failed)
        ->and($subscription->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($payment->raw_payload['rejection_reason'])->toBe('Dana tidak masuk');
});

it('throws InvalidWebhookSignature when webhook is received', function () {
    $this->gateway->parseWebhook(Request::create('/webhooks/payments/bank_transfer', 'POST'));
})->throws(InvalidWebhookSignature::class);
