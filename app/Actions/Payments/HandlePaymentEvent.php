<?php

namespace App\Actions\Payments;

use App\Actions\Subscription\SubscriptionPaymentHandler;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Payments\PaymentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a gateway notification exactly once per payment (docs/logic/subscription.md → Alur 3).
 */
class HandlePaymentEvent
{
    /** @var array<class-string, class-string<HandlesPayment>> */
    public const HANDLERS = [
        Subscription::class => SubscriptionPaymentHandler::class,
    ];

    public function handle(PaymentEvent $event): ?Payment
    {
        return DB::transaction(function () use ($event) {
            $payment = Payment::where('gateway_reference', $event->reference)->lockForUpdate()->first();

            if ($payment === null) {
                Log::warning("Payment event for unknown reference [{$event->reference}].");

                return null;
            }

            if ($payment->status !== PaymentStatus::Pending || $event->status === PaymentStatus::Pending) {
                return $payment;
            }

            $payment->update([
                'status' => $event->status,
                'paid_at' => $event->status === PaymentStatus::Paid ? now() : null,
                'raw_payload' => $event->payload,
            ]);

            $handler = app(self::HANDLERS[$payment->payable::class]);

            $event->status === PaymentStatus::Paid ? $handler->paid($payment) : $handler->failed($payment);

            return $payment;
        });
    }
}
