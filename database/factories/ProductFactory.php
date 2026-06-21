<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TherapeuticArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'therapeutic_area_id' => TherapeuticArea::factory(),
            'name' => ucfirst(fake()->unique()->word()).' '.fake()->numberBetween(50, 500),
            'slug' => null,
            'category' => fake()->randomElement(['Tablet', 'Capsule', 'Syrup', 'Injection']),
            'composition' => fake()->words(2, true),
            'strength' => fake()->numberBetween(50, 500).' mg',
            'packaging' => fake()->randomElement(['10x10 strip', '10x15 strip', '60 ml bottle']),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
