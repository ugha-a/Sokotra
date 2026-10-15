<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Manual bank transfer to the platform/admin bank account (configured in platform_settings).
 * Buyers are directed to /payments/{payment} to view account details and upload proof of transfer.
 * Verification is performed by admin in the admin panel.
 */
class BankTransferPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'bank_transfer';
    }

    public function createCheckout(Payment $payment): Checkout
    {
        $reference = 'TRF-'.$payment->id.'-'.Str::upper(Str::random(6));

        return new Checkout($reference, route('payments.show', $payment));
    }

    public function parseWebhook(Request $request): PaymentEvent
    {
        throw new InvalidWebhookSignature('Transfer bank manual tidak menggunakan webhook; verifikasi dilakukan oleh admin.');
    }
}
