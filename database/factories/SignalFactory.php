<?php

namespace Database\Factories;

use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Enums\SignalStatus;
use App\Enums\SignalVisibility;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Defaults to an active EURUSD market buy: entry 1.08000, SL −20 pips, TP +50 pips.
 *
 * @extends Factory<Signal>
 */
class SignalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trader_id' => User::factory(),
            'instrument_id' => fn () => Instrument::where('symbol', 'EURUSD')->value('id'),
            'pip_size_snapshot' => '0.0001',
            'spread_pips_snapshot' => '1.00',
            'digits_snapshot' => 5,
            'direction' => SignalDirection::Buy,
            'order_type' => OrderType::Market,
            'entry_price' => '1.08000',
            'stop_loss' => '1.07800',
            'take_profit' => '1.08500',
            'rr' => 2.5,
            'sl_pips' => 20,
            'visibility' => SignalVisibility::Free,
            'analysis' => 'Pantulan dari support harian dengan struktur higher low di H4.',
            'status' => SignalStatus::Active,
            'published_at' => now(),
            'triggered_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'order_type' => OrderType::Limit,
            'entry_price' => '1.07900',
            'stop_loss' => '1.07700',
            'take_profit' => '1.08400',
            'status' => SignalStatus::Pending,
            'triggered_at' => null,
            'expires_at' => now()->addDay(),
        ]);
    }
}
