<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('VC####??')),
            'type' => Voucher::TYPE_FIXED,
            'value' => 50000,
            'per_user_limit' => 1,
            'is_active' => true,
        ];
    }

    public function percent(float $percent, ?int $maxDiscount = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => Voucher::TYPE_PERCENT,
            'value' => $percent,
            'max_discount' => $maxDiscount,
        ]);
    }
}
