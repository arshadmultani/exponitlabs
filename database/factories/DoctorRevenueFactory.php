<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<DoctorRevenue>
 */
class DoctorRevenueFactory extends Factory
{
    protected $model = DoctorRevenue::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'period_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'source' => 'manual_entry',
            'notes' => fake()->optional()->sentence(),
            'created_by_user_id' => User::factory(),
        ];
    }

    public function csvImported(): static
    {
        return $this->state(fn () => ['source' => 'csv_import']);
    }

    public function forPeriod(Carbon|string $period): static
    {
        $date = $period instanceof Carbon ? $period : Carbon::parse($period);

        return $this->state(fn () => [
            'period_date' => $date->startOfMonth()->format('Y-m-d'),
        ]);
    }
}
