<?php

namespace Database\Factories;

use App\Models\Popup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Popup>
 */
class PopupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->sentence(),
            'media_type' => 'none',
            'dismiss_minutes' => 60,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
