<?php

namespace Database\Factories;

use App\Enums\AffiliateWithdrawalStatus;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateWithdrawal>
 */
class AffiliateWithdrawalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => 50000,
            'method' => AffiliateWithdrawal::METHOD_BANK,
            'bank_name' => 'MB Bank',
            'bank_account_number' => '0123456789',
            'bank_account_name' => 'NGUYEN VAN A',
            'status' => AffiliateWithdrawalStatus::Pending,
        ];
    }
}
