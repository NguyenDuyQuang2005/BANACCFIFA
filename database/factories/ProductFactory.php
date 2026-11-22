<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Acc FC '.fake()->unique()->numerify('####');

        return [
            'category_id' => Category::factory(),
            'code' => 'DT'.fake()->unique()->numerify('####'),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'name' => $name,
            'description' => fake()->sentence(12),
            'price' => fake()->numberBetween(150000, 5000000),
            'original_price' => null,
            'bp' => fake()->numberBetween(1, 40) * 1_000_000_000,
            'squad' => fake()->randomElement(['4-3-3', '4-2-3-1', '5-3-2']),
            'ovr' => fake()->numberBetween(90, 130),
            'featured_players' => [],
            'status' => ProductStatus::Available,
            'is_featured' => false,
            'sold_at' => null,
            'credentials' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
            'status' => ProductStatus::Available,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Sold,
            'sold_at' => now(),
            'credentials' => 'game-user:secret-pass',
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Hidden,
        ]);
    }
}
