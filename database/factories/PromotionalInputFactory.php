<?php

namespace Database\Factories;

use App\Models\PromotionalInput;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionalInput>
 */
class PromotionalInputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
