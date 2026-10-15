<?php

namespace App\Events;

use App\Models\Signal;
use Illuminate\Foundation\Events\Dispatchable;

class SignalPublished
{
    use Dispatchable;

    public function __construct(public Signal $signal) {}
}
