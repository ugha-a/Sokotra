<?php

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_account_id' => fn (array $attributes) => BankAccount::factory()->create(['user_id' => $attributes['user_id']])->id,
            'amount' => 150000,
            'fee' => 6500,
            'net_amount' => 150000,
            'status' => WithdrawalStatus::Paid,
        ];
    }
}
