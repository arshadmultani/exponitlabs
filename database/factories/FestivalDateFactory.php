<?php

namespace Database\Factories;

use App\Models\Festival;
use App\Models\FestivalDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FestivalDate>
 */
class FestivalDateFactory extends Factory
{
    protected $model = FestivalDate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('2025-01-01', '2030-12-31');

        return [
            'festival_id' => Festival::factory(),
            'year' => (int) $date->format('Y'),
            'date' => $date->format('Y-m-d'),
            'is_manually_adjusted' => false,
            'adjusted_by_user_id' => null,
            'adjustment_notes' => null,
        ];
    }

    /**
     * Indicate that the date has been manually adjusted.
     */
    public function manuallyAdjusted(string $notes = 'Moon sighting adjustment'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manually_adjusted' => true,
            'adjustment_notes' => $notes,
        ]);
    }
}
