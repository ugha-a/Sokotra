<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Creates a pending payment for a payable and asks the gateway for a checkout URL.
 */
class StartCheckout
{
    public function __construct(private PaymentGateway $gateway) {}

    public function handle(User $buyer, Model $payable, int $amount): Payment
    {
        $payment = Payment::create([
            'user_id' => $buyer->id,
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'amount' => $amount,
            'gateway' => $this->gateway->name(),
            'gateway_reference' => 'pending-'.Str::uuid(),
            'status' => PaymentStatus::Pending,
            'expires_at' => now()->addHours((int) config('services.payments.checkout_expiry_hours', 24)),
        ]);

        try {
            $checkout = $this->gateway->createCheckout($payment);
        } catch (RuntimeException $exception) {
            report($exception);

            // The caller's transaction rolls back the payment and the payable.
            throw ValidationException::withMessages(['checkout' => 'Pembayaran sedang tidak bisa diproses. Coba lagi beberapa saat lagi.']);
        }

        $payment->update(['gateway_reference' => $checkout->reference, 'checkout_url' => $checkout->url]);

        return $payment;
    }
}
