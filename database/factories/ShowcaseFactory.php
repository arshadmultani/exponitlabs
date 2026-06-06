<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Showcase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Showcase>
 */
class ShowcaseFactory extends Factory
{
    protected $model = Showcase::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'title' => fake()->words(2, true),
            'description' => null,
            'media_type' => 'text',
            'media_url' => null,
        ];
    }

    public function text(): static
    {
        return $this->state(fn () => [
            'media_type' => 'text',
            'description' => '<p>'.fake()->paragraph().'</p>',
            'media_url' => null,
        ]);
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'media_type' => 'image',
            'description' => null,
            'media_url' => 'microsite/showcases/'.fake()->uuid().'.jpg',
        ]);
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'media_type' => 'video',
            'description' => null,
            'media_url' => 'microsite/showcases/'.fake()->uuid().'.mp4',
        ]);
    }
}
