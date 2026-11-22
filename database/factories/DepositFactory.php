<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'code' => 'DT'.fake()->unique()->numerify('########'),
            'amount' => 100000,
            'status' => DepositStatus::Pending,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DepositStatus::Success,
            'confirmed_at' => now(),
        ]);
    }
}
