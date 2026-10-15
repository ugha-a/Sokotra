<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_id' => fn () => Bank::where('code', 'BCA')->value('id'),
            'account_number' => fake()->numerify('##########'),
            'account_name' => fake()->name(),
        ];
    }
}
