<?php

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\TraderProfile;
use App\Services\Payments\InvalidWebhookSignature;
use App\Services\Payments\MidtransPaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->gateway = new MidtransPaymentGateway('SB-Mid-server-test', false);
    app()->instance(PaymentGateway::class, $this->gateway);
});

function midtransSignature(string $orderId, string $status, string $amount, string $key = 'SB-Mid-server-test'): string
{
    return hash('sha512', $orderId.$status.$amount.$key);
}

it('creates a Snap transaction and returns its redirect URL', function () {
    Http::fake(['app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'tok', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/tok'], 201)]);

    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);
    $this->actingAs($member)->post(route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')), ['months' => 1, 'accept_risk' => true]);

    $payment = Payment::firstOrFail();

    expect($payment->gateway)->toBe('midtrans')
        ->and($payment->gateway_reference)->toStartWith("SKT-{$payment->id}-")
        ->and($payment->checkout_url)->toBe('https://app.sandbox.midtrans.com/snap/v4/redirection/tok');

    Http::assertSent(fn (HttpRequest $request) => $request['transaction_details']['gross_amount'] === 150000
        && $request['transaction_details']['order_id'] === $payment->gateway_reference
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('SB-Mid-server-test:')));
});

it('verifies notification signatures and maps transaction statuses', function (string $transactionStatus, ?string $fraud, PaymentStatus $expected) {
    $payload = [
        'order_id' => 'SKT-1-ABC123',
        'status_code' => '200',
        'gross_amount' => '150000.00',
        'transaction_status' => $transactionStatus,
        'fraud_status' => $fraud,
    ];
    $payload['signature_key'] = midtransSignature($payload['order_id'], $payload['status_code'], $payload['gross_amount']);

    $event = $this->gateway->parseWebhook(Request::create('/webhooks/payments/midtrans', 'POST', $payload));

    expect($event->reference)->toBe('SKT-1-ABC123')->and($event->status)->toBe($expected);
})->with([
    'settlement' => ['settlement', null, PaymentStatus::Paid],
    'capture accepted' => ['capture', 'accept', PaymentStatus::Paid],
    'capture challenged' => ['capture', 'challenge', PaymentStatus::Pending],
    'pending' => ['pending', null, PaymentStatus::Pending],
    'expired' => ['expire', null, PaymentStatus::Expired],
    'denied' => ['deny', null, PaymentStatus::Failed],
    'cancelled' => ['cancel', null, PaymentStatus::Failed],
]);

it('rejects forged notifications', function () {
    $this->gateway->parseWebhook(Request::create('/webhooks/payments/midtrans', 'POST', [
        'order_id' => 'SKT-1-ABC123',
        'status_code' => '200',
        'gross_amount' => '150000.00',
        'transaction_status' => 'settlement',
        'signature_key' => 'forged',
    ]));
})->throws(InvalidWebhookSignature::class);

it('activates a subscription from a real Midtrans notification end to end', function () {
    Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['token' => 't', 'redirect_url' => 'https://example.test/pay'], 201)]);
    $trader = premiumTrader();
    $member = userWithRoles(Role::Member);
    $this->actingAs($member)->post(route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')), ['months' => 1, 'accept_risk' => true]);
    $payment = Payment::firstOrFail();
    auth()->logout();

    $payload = ['order_id' => $payment->gateway_reference, 'status_code' => '200', 'gross_amount' => '150000.00', 'transaction_status' => 'settlement'];
    $payload['signature_key'] = midtransSignature($payload['order_id'], '200', '150000.00');

    $this->postJson(route('webhooks.payments', 'midtrans'), $payload)->assertOk();

    expect(Subscription::firstOrFail()->status->value)->toBe('active');
});

it('shows a friendly error and keeps nothing when Midtrans is down', function () {
    Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['error_messages' => ['down']], 503)]);
    $trader = premiumTrader();

    $this->actingAs(userWithRoles(Role::Member))
        ->post(route('subscriptions.store', TraderProfile::where('user_id', $trader->id)->value('id')), ['months' => 1, 'accept_risk' => true])
        ->assertSessionHasErrors('checkout');

    expect(Payment::count())->toBe(0)->and(Subscription::count())->toBe(0);
});
