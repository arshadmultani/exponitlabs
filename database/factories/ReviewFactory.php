<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'reviewer_name' => fake()->name(),
            'submitted_by_name' => fake()->name(),
            'rating' => fake()->numberBetween(3, 5),
            'review_text' => fake()->sentence(),
            'media_url' => null,
            'media_type' => null,
            'status' => 'pending',
            'verified_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'verified_at' => null,
        ]);
    }
}
