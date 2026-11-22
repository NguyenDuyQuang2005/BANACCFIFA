<?php

namespace Database\Factories;

use App\Enums\BankTransactionStatus;
use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankTransaction>
 */
class BankTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'sepay',
            'provider_transaction_id' => (string) fake()->unique()->numberBetween(1000, 999999),
            'gateway' => 'MBBank',
            'account_number' => '0123456789',
            'amount' => 100000,
            'content' => 'Chuyen tien',
            'transacted_at' => now(),
            'status' => BankTransactionStatus::Unmatched,
        ];
    }
}
