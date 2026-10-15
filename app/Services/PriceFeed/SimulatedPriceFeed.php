<?php

namespace App\Services\PriceFeed;

use App\Models\Instrument;
use App\Support\Signal\PriceMath;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Deterministic synthetic prices for local development, so signals can be
 * published and evaluated without a market data subscription. Never use in production.
 */
class SimulatedPriceFeed implements PriceFeed
{
    /**
     * @param  array<string, float>  $basePrices  Keyed by instrument symbol.
     */
    public function __construct(private array $basePrices) {}

    public function quote(Instrument $instrument): Quote
    {
        $math = PriceMath::forInstrument($instrument);
        $now = CarbonImmutable::now();
        $bid = $math->points($this->priceAt($instrument, $now->getTimestamp()));

        return new Quote($math->price($bid), $math->price($bid + $math->spreadPoints()), $now);
    }

    public function candles(Instrument $instrument, CarbonInterface $from, CarbonInterface $to): array
    {
        $math = PriceMath::forInstrument($instrument);
        $candles = [];

        for ($minute = CarbonImmutable::instance($from)->startOfMinute(); $minute->lt($to); $minute = $minute->addMinute()) {
            $start = $minute->getTimestamp();
            $open = $this->priceAt($instrument, $start);
            $close = $this->priceAt($instrument, $start + 60);
            $wick = abs($open - $close) * 0.5 + $this->base($instrument) * 0.00008;

            $candles[] = new Candle(
                $minute,
                $math->price($math->points($open)),
                $math->price($math->points(max($open, $close) + $wick)),
                $math->price($math->points(min($open, $close) - $wick)),
                $math->price($math->points($close)),
            );
        }

        return $candles;
    }

    private function priceAt(Instrument $instrument, int $timestamp): float
    {
        $base = $this->base($instrument);
        $seed = crc32($instrument->symbol);
        $drift = 0.004 * sin(($timestamp + $seed) / 21600)
            + 0.0012 * sin(($timestamp + $seed) / 1800)
            + 0.0004 * sin(($timestamp + $seed) / 240);

        return $base * (1 + $drift);
    }

    private function base(Instrument $instrument): float
    {
        return (float) ($this->basePrices[$instrument->symbol] ?? 1.0);
    }
}
