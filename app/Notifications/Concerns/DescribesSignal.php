<?php

namespace App\Notifications\Concerns;

use App\Models\Signal;

trait DescribesSignal
{
    private function signalName(Signal $signal): string
    {
        $trader = $signal->trader->traderProfile->display_name ?? $signal->trader->name;

        return "{$signal->instrument->symbol} {$signal->direction->label()} dari {$trader}";
    }

    private function traderUrl(Signal $signal): string
    {
        $slug = $signal->trader->traderProfile?->slug;

        return $slug ? route('traders.show', $slug) : route('signals.index');
    }
}
