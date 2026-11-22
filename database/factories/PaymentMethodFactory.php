<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Vietcombank',
            'account_name' => 'DINH TIEN FCO',
            'account_number' => fake()->unique()->numerify('##########'),
            'bank_code' => 'VCB',
            'qr_image_path' => null,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
