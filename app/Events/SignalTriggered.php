<?php

namespace App\Events;

use App\Models\Signal;
use Illuminate\Foundation\Events\Dispatchable;

class SignalTriggered
{
    use Dispatchable;

    public function __construct(public Signal $signal) {}
}
