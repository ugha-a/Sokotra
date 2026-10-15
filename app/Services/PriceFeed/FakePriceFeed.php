<?php

namespace App\Services\PriceFeed;

use App\Models\Instrument;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * In-memory feed for tests: quotes and candles are injected per symbol.
 */
class FakePriceFeed implements PriceFeed
{
    /** @var array<string, array{bid: string, ask: string}> */
    private array $quotes = [];

    /** @var array<string, list<Candle>> */
    private array $candles = [];

    /** @var array<string, true> */
    private array $failing = [];

    /** @var array<string, list<array{from: CarbonImmutable, to: CarbonImmutable}>> */
    private array $requests = [];

    public function setQuote(string $symbol, string $bid, string $ask): self
    {
        $this->quotes[$symbol] = ['bid' => $bid, 'ask' => $ask];

        return $this;
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: string}>  $candles  [time, open, high, low, close]
     */
    public function addCandles(string $symbol, array $candles): self
    {
        foreach ($candles as [$time, $open, $high, $low, $close]) {
            $this->candles[$symbol][] = new Candle(CarbonImmutable::parse($time)->startOfMinute(), $open, $high, $low, $close);
        }

        return $this;
    }

    public function failFor(string $symbol): self
    {
        $this->failing[$symbol] = true;

        return $this;
    }

    public function recover(string $symbol): self
    {
        unset($this->failing[$symbol]);

        return $this;
    }

    /**
     * @return list<array{from: CarbonImmutable, to: CarbonImmutable}>
     */
    public function requests(string $symbol): array
    {
        return $this->requests[$symbol] ?? [];
    }

    public function quote(Instrument $instrument): Quote
    {
        $this->guard($instrument);

        $quote = $this->quotes[$instrument->symbol]
            ?? throw new PriceFeedUnavailable("No fake quote for {$instrument->symbol}.");

        return new Quote($quote['bid'], $quote['ask'], CarbonImmutable::now());
    }

    public function candles(Instrument $instrument, CarbonInterface $from, CarbonInterface $to): array
    {
        $this->requests[$instrument->symbol][] = ['from' => CarbonImmutable::instance($from), 'to' => CarbonImmutable::instance($to)];
        $this->guard($instrument);

        $candles = array_values(array_filter(
            $this->candles[$instrument->symbol] ?? [],
            fn (Candle $candle) => $candle->time->gte($from) && $candle->time->lt($to),
        ));

        usort($candles, fn (Candle $a, Candle $b) => $a->time <=> $b->time);

        return $candles;
    }

    private function guard(Instrument $instrument): void
    {
        if (isset($this->failing[$instrument->symbol])) {
            throw new PriceFeedUnavailable("Fake feed failing for {$instrument->symbol}.");
        }
    }
}
