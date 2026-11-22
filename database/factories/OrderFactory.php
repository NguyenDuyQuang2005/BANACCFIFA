<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'DH'.fake()->unique()->bothify('????????'),
            'total' => 150000,
            'status' => OrderStatus::Completed,
            'customer_phone_masked' => '090***123',
            'completed_at' => now(),
        ];
    }
}
