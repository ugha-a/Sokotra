<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Payment provider (docs/logic/subscription.md → Alur). Drivers: simulated (local
 * dev), fake (tests); Midtrans/Xendit are added once credentials exist.
 */
interface PaymentGateway
{
    /** Name used in payments.gateway and in the webhook URL. */
    public function name(): string;

    /** Create the charge and return where to send the buyer. */
    public function createCheckout(Payment $payment): Checkout;

    /**
     * Verify and parse an incoming webhook.
     *
     * @throws InvalidWebhookSignature
     */
    public function parseWebhook(Request $request): PaymentEvent;
}
