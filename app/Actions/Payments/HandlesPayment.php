<?php

namespace App\Actions\Payments;

use App\Models\Payment;

/**
 * What happens to a payable (subscription, order) when its payment settles.
 */
interface HandlesPayment
{
    public function paid(Payment $payment): void;

    public function failed(Payment $payment): void;
}
