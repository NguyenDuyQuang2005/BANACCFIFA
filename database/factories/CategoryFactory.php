<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'icon' => null,
            'image_path' => null,
            'sort_order' => fake()->numberBetween(1, 20),
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
