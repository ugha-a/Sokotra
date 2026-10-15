<?php

namespace App\Http\Controllers\Trader;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\PriceFeedUnavailable;
use Illuminate\Http\JsonResponse;

/**
 * Live bid/ask for the signal form.
 */
class QuoteController extends Controller
{
    public function show(Instrument $instrument, PriceFeed $feed): JsonResponse
    {
        try {
            $quote = $feed->quote($instrument);
        } catch (PriceFeedUnavailable) {
            return response()->json(['message' => "Harga live {$instrument->symbol} sedang tidak tersedia."], 503);
        }

        return response()->json([
            'bid' => $quote->bid,
            'ask' => $quote->ask,
            'time' => $quote->time->toIso8601String(),
        ]);
    }
}
