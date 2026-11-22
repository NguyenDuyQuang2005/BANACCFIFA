<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => 'ST',
            'ovr' => fake()->numberBetween(100, 136),
            'pace' => fake()->numberBetween(100, 140),
            'stamina' => fake()->numberBetween(100, 140),
            'stars' => fake()->numberBetween(1, 5),
            'is_visible' => true,
        ];
    }
}
