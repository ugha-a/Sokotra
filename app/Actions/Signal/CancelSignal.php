<?php

namespace App\Actions\Signal;

use App\Enums\SignalStatus;
use App\Events\SignalClosed;
use App\Models\Signal;
use App\Support\Settings;
use Illuminate\Validation\ValidationException;

/**
 * Traders may cancel pending signals only, a limited number of times per week.
 */
class CancelSignal
{
    public function handle(Signal $signal): Signal
    {
        if ($signal->status !== SignalStatus::Pending) {
            throw ValidationException::withMessages(['signal' => 'Hanya sinyal yang menunggu entry yang bisa dibatalkan.']);
        }

        $max = Settings::get('signal.max_cancel_per_week');
        $cancelledThisWeek = Signal::query()
            ->where('trader_id', $signal->trader_id)
            ->where('status', SignalStatus::Cancelled)
            ->where('closed_at', '>=', now()->startOfWeek())
            ->count();

        if ($cancelledThisWeek >= $max) {
            throw ValidationException::withMessages(['signal' => "Batas pembatalan minggu ini ({$max}x) sudah tercapai."]);
        }

        $signal->update(['status' => SignalStatus::Cancelled, 'closed_at' => now()]);
        $signal->events()->create(['type' => 'cancelled', 'occurred_at' => now()]);

        SignalClosed::dispatch($signal);

        return $signal;
    }
}
