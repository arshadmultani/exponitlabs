<?php

namespace Database\Factories;

use App\Models\TherapeuticArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TherapeuticArea>
 */
class TherapeuticAreaFactory extends Factory
{
    protected $model = TherapeuticArea::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => null,
            'icon' => null,
            'summary' => fake()->sentence(12),
            'accent_color' => '#1FB6AA',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
