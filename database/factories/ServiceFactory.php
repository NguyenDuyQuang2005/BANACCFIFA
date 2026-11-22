<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'DV'.fake()->unique()->numerify('###'),
            'name' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_visible' => false,
        ]);
    }
}
