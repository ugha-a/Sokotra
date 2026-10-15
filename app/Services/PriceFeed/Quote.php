<?php

namespace App\Services\PriceFeed;

use Carbon\CarbonImmutable;

final readonly class Quote
{
    public function __construct(
        public string $bid,
        public string $ask,
        public CarbonImmutable $time,
    ) {}
}
