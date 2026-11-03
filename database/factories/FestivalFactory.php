<?php

namespace Database\Factories;

use App\Models\Festival;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Festival>
 */
class FestivalFactory extends Factory
{
    protected $model = Festival::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'category' => 'cultural_and_national',
            'target_specialties' => null,
            'days_before' => 3,
            'days_after' => 1,
            'greeting_message_template' => 'Wishing you a joyful and blessed celebration!',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the festival is a clinical observance.
     *
     * @param  list<string>  $specialties
     */
    public function clinical(array $specialties = ['Cardiology']): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'clinical_observance',
            'target_specialties' => $specialties,
        ]);
    }
}
