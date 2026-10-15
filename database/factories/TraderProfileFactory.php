<?php

namespace Database\Factories;

use App\Enums\TraderStatus;
use App\Enums\TradingStyle;
use App\Models\Instrument;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TraderProfile>
 */
class TraderProfileFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->firstName().' '.fake()->randomElement(['FX', 'Trader', 'Swing', 'Gold']);

        return [
            'user_id' => User::factory(),
            'display_name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'bio' => 'Trader '.fake()->sentence(8),
            'trading_style' => fake()->randomElement(TradingStyle::cases()),
            'experience_years' => fake()->numberBetween(1, 10),
            'main_instruments' => Instrument::query()->inRandomOrder()->limit(2)->pluck('id')->all(),
            'status' => TraderStatus::Applied,
            'applied_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => TraderStatus::Approved,
            'approved_at' => now(),
            'reviewed_at' => now(),
            'rank_id' => Rank::query()->orderBy('level')->value('id'),
        ]);
    }
}
