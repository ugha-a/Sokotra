<?php

namespace App\Events;

use App\Models\Signal;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched for every final state; statistics and notifications listen here.
 */
class SignalClosed
{
    use Dispatchable;

    public function __construct(public Signal $signal) {}
}
