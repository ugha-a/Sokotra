<?php

namespace App\Services\PriceFeed;

use App\Models\Instrument;
use App\Support\Signal\PriceMath;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Twelve Data (https://twelvedata.com) REST driver. Uses instruments.feed_symbol
 * (e.g. "EUR/USD", "XAU/USD"). Twelve Data prices are treated as bid; the ask adds
 * the instrument's configured spread. Check the plan's credit limits before going live.
 */
class TwelveDataPriceFeed implements PriceFeed
{
    public function __construct(private string $apiKey, private string $baseUrl = 'https://api.twelvedata.com') {}

    public function quote(Instrument $instrument): Quote
    {
        $body = $this->get('/price', ['symbol' => $this->symbol($instrument)]);

        if (! isset($body['price']) || ! is_numeric($body['price'])) {
            throw new PriceFeedUnavailable("Twelve Data returned no price for {$instrument->symbol}.");
        }

        $math = PriceMath::forInstrument($instrument);
        $bid = $math->points((string) $body['price']);

        return new Quote($math->price($bid), $math->price($bid + $math->spreadPoints()), CarbonImmutable::now());
    }

    public function candles(Instrument $instrument, CarbonInterface $from, CarbonInterface $to): array
    {
        $body = $this->get('/time_series', [
            'symbol' => $this->symbol($instrument),
            'interval' => '1min',
            'start_date' => CarbonImmutable::instance($from)->utc()->format('Y-m-d H:i:s'),
            'end_date' => CarbonImmutable::instance($to)->utc()->format('Y-m-d H:i:s'),
            'timezone' => 'UTC',
            'order' => 'ASC',
            'outputsize' => 5000,
        ], allowNoData: true);

        $candles = [];

        foreach ($body['values'] ?? [] as $row) {
            $time = CarbonImmutable::parse((string) $row['datetime'], 'UTC');

            if ($time->gte($from) && $time->lt($to)) {
                $candles[] = new Candle($time, (string) $row['open'], (string) $row['high'], (string) $row['low'], (string) $row['close']);
            }
        }

        return $candles;
    }

    /**
     * @param  array<string, string|int>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query, bool $allowNoData = false): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)->timeout(10)->retry(2, 200, throw: false)
                ->get($path, [...$query, 'apikey' => $this->apiKey]);
        } catch (ConnectionException $exception) {
            throw new PriceFeedUnavailable('Twelve Data unreachable: '.$exception->getMessage());
        }

        $body = $this->decode($response);

        if (($body['status'] ?? 'ok') === 'error') {
            $message = (string) ($body['message'] ?? 'unknown error');

            // Weekends and holidays come back as an error with no data: that is a closed market.
            if ($allowNoData && str_contains(strtolower($message), 'no data')) {
                return ['values' => []];
            }

            throw new PriceFeedUnavailable("Twelve Data error: {$message}");
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        if ($response->failed()) {
            throw new PriceFeedUnavailable("Twelve Data HTTP {$response->status()}.");
        }

        $body = $response->json();

        return is_array($body) ? $body : throw new PriceFeedUnavailable('Twelve Data returned invalid JSON.');
    }

    private function symbol(Instrument $instrument): string
    {
        return $instrument->feed_symbol ?: $instrument->symbol;
    }
}
