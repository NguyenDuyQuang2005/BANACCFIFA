<?php

namespace Database\Factories;

use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateCommission>
 */
class AffiliateCommissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_user_id' => User::factory(),
            'order_id' => Order::factory(),
            'order_total' => 200000,
            'rate' => 5,
            'amount' => 10000,
        ];
    }
}
