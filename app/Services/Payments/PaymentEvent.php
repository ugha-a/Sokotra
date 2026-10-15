<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;

/**
 * Normalised gateway notification.
 */
final readonly class PaymentEvent
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $reference,
        public PaymentStatus $status,
        public array $payload,
    ) {}
}
