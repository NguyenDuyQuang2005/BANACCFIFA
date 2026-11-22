<?php

namespace Database\Factories;

use App\Models\CoachSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachSkill>
 */
class CoachSkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'usage_percent' => fake()->randomFloat(2, 0, 25),
            'is_visible' => true,
        ];
    }
}
