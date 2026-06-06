<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('98########'),
            'specialty' => fake()->randomElement(['Cardiologist', 'Dermatologist', 'Paediatrician', 'Dentist']),
            'qualification' => fake()->randomElement(['MBBS', 'MBBS, MD', 'BDS', 'MBBS, MS']),
            'profile_photo' => null,
            'town' => fake()->city(),
            'address' => fake()->streetAddress(),
            'practice_since' => fake()->dateTimeBetween('-20 years', '-1 year'),
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
