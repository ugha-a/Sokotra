<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Test double: deterministic references and a trivially checked signature header.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createCheckout(Payment $payment): Checkout
    {
        $reference = 'fake-'.Str::lower(Str::random(12));

        return new Checkout($reference, "https://pay.fake.test/{$reference}");
    }

    public function parseWebhook(Request $request): PaymentEvent
    {
        if ($request->header('X-Fake-Signature') !== 'valid') {
            throw new InvalidWebhookSignature('Invalid fake signature.');
        }

        return new PaymentEvent(
            (string) $request->input('reference'),
            PaymentStatus::from((string) $request->input('status')),
            $request->all(),
        );
    }
}
