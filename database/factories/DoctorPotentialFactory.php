<?php

namespace Database\Factories;

use App\Models\DoctorPotential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorPotential>
 */
class DoctorPotentialFactory extends Factory
{
    protected $model = DoctorPotential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Nascent',
            'code' => 'nascent',
            'color' => 'warning',
            'sort_order' => 3,
            'target_visits_per_month' => 1,
            'is_active' => true,
        ];
    }
}
