<?php

use App\Models\Instrument;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\PriceFeedUnavailable;
use App\Services\PriceFeed\TwelveDataPriceFeed;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->feed = new TwelveDataPriceFeed('test-key', 'https://api.twelvedata.com');
    $this->eurusd = Instrument::where('symbol', 'EURUSD')->firstOrFail();
});

it('quotes the price as bid and adds the instrument spread for the ask', function () {
    Http::fake(['api.twelvedata.com/price*' => Http::response(['price' => '1.08345'])]);

    $quote = $this->feed->quote($this->eurusd);

    expect($quote->bid)->toBe('1.08345')
        ->and($quote->ask)->toBe('1.08355');

    Http::assertSent(fn (Request $request) => $request['symbol'] === 'EUR/USD' && $request['apikey'] === 'test-key');
});

it('returns completed 1-minute candles inside the window, oldest first', function () {
    Http::fake(['api.twelvedata.com/time_series*' => Http::response([
        'status' => 'ok',
        'values' => [
            ['datetime' => '2026-10-16 10:00:00', 'open' => '1.08000', 'high' => '1.08100', 'low' => '1.07950', 'close' => '1.08050'],
            ['datetime' => '2026-10-16 10:01:00', 'open' => '1.08050', 'high' => '1.08200', 'low' => '1.08000', 'close' => '1.08150'],
            ['datetime' => '2026-10-16 10:02:00', 'open' => '1.08150', 'high' => '1.08250', 'low' => '1.08100', 'close' => '1.08200'],
        ],
    ])]);

    $candles = $this->feed->candles($this->eurusd, CarbonImmutable::parse('2026-10-16 10:00'), CarbonImmutable::parse('2026-10-16 10:02'));

    expect($candles)->toHaveCount(2)
        ->and($candles[0]->time->format('H:i'))->toBe('10:00')
        ->and($candles[1]->high)->toBe('1.08200');

    Http::assertSent(fn (Request $request) => $request['interval'] === '1min' && $request['timezone'] === 'UTC' && $request['order'] === 'ASC');
});

it('treats "no data" as a closed market, not an outage', function () {
    Http::fake(['api.twelvedata.com/time_series*' => Http::response(['status' => 'error', 'code' => 400, 'message' => 'No data is available on the specified dates.'])]);

    expect($this->feed->candles($this->eurusd, CarbonImmutable::parse('2026-10-17 10:00'), CarbonImmutable::parse('2026-10-17 11:00')))->toBe([]);
});

it('reports provider failures as unavailable', function (array|int $response) {
    Http::fake(['api.twelvedata.com/*' => is_int($response) ? Http::response('', $response) : Http::response($response)]);

    $this->feed->candles($this->eurusd, CarbonImmutable::parse('2026-10-16 10:00'), CarbonImmutable::parse('2026-10-16 10:05'));
})->with([
    'server error' => [500],
    'rate limited' => [['status' => 'error', 'code' => 429, 'message' => 'You have run out of API credits.']],
])->throws(PriceFeedUnavailable::class);

it('is selected by the price feed driver setting', function () {
    config(['services.price_feed.driver' => 'twelvedata', 'services.price_feed.twelvedata.key' => 'abc']);
    app()->forgetInstance(PriceFeed::class);

    expect(app(PriceFeed::class))->toBeInstanceOf(TwelveDataPriceFeed::class);
});
