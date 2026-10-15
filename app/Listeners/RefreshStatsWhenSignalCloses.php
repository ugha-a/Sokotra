<?php

namespace App\Listeners;

use App\Actions\Ranking\RefreshTraderStats;
use App\Events\SignalClosed;

class RefreshStatsWhenSignalCloses
{
    public function __construct(private RefreshTraderStats $refresh) {}

    public function handle(SignalClosed $event): void
    {
        $this->refresh->handle($event->signal->trader);
    }
}
