<?php

namespace Database\Factories;

use App\Models\SkillMove;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SkillMove>
 */
class SkillMoveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'level' => fake()->numberBetween(1, 5),
            'description' => fake()->sentence(),
            'keyboard_keys' => 'SHIFT + Q',
            'controller_keys' => 'RB + LS',
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
