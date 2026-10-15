<?php

namespace App\Services\PriceFeed;

use Carbon\CarbonImmutable;

/**
 * One-minute candle of bid prices; $time is the minute it starts.
 */
final readonly class Candle
{
    public function __construct(
        public CarbonImmutable $time,
        public string $open,
        public string $high,
        public string $low,
        public string $close,
    ) {}
}
