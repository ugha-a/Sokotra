<?php

namespace Database\Factories;

use App\Enums\KycStatus;
use App\Models\KycVerification;
use App\Models\User;
use App\Support\Nik;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KycVerification>
 */
class KycVerificationFactory extends Factory
{
    public function definition(): array
    {
        $nik = fake()->unique()->numerify('################');

        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'nik' => $nik,
            'nik_hash' => Nik::hash($nik),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'id_card_path' => fn (array $attributes) => "kyc/{$attributes['user_id']}/id-card.jpg",
            'selfie_path' => fn (array $attributes) => "kyc/{$attributes['user_id']}/selfie.jpg",
            'status' => KycStatus::Submitted,
            'submitted_at' => now(),
        ];
    }
}
