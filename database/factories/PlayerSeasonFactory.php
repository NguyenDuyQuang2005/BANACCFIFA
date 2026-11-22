<?php

namespace Database\Factories;

use App\Models\PlayerSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerSeason>
 */
class PlayerSeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'sort_order' => 0,
        ];
    }
}
