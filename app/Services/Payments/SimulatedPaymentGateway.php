<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Local development only: the "payment page" is /payments/{payment}/simulate,
 * where the buyer chooses the outcome. Never use in production.
 */
class SimulatedPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulated';
    }

    public function createCheckout(Payment $payment): Checkout
    {
        return new Checkout('sim-'.Str::lower(Str::random(16)), route('payments.simulate.show', $payment));
    }

    public function parseWebhook(Request $request): PaymentEvent
    {
        throw new InvalidWebhookSignature('The simulated gateway has no webhooks; use the simulate page.');
    }
}
