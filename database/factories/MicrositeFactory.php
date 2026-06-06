<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Microsite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Microsite>
 */
class MicrositeFactory extends Factory
{
    protected $model = Microsite::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'url' => fake()->unique()->slug(3).'-'.fake()->bothify('??????'),
            'is_active' => true,
            'design_settings' => ['bg_color' => '#24669B'],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
