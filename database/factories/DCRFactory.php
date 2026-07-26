<?php

namespace Database\Factories;

use App\Models\DCR;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DCR>
 */
class DCRFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'doctor_id' => Doctor::factory(),
            'remarks' => fake()->sentence(),
        ];
    }
}
