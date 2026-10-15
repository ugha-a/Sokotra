<?php

namespace App\Services\PriceFeed;

use App\Models\Instrument;
use Carbon\CarbonInterface;

/**
 * Market data source for publishing and evaluating signals (docs/logic/signal-lifecycle.md).
 * Drivers: simulated (local dev), fake (tests); a real provider is added later.
 */
interface PriceFeed
{
    /**
     * Current bid/ask.
     *
     * @throws PriceFeedUnavailable
     */
    public function quote(Instrument $instrument): Quote;

    /**
     * Completed 1-minute bid candles whose start time is in [$from, $to), oldest first.
     *
     * @return list<Candle>
     *
     * @throws PriceFeedUnavailable
     */
    public function candles(Instrument $instrument, CarbonInterface $from, CarbonInterface $to): array;
}
