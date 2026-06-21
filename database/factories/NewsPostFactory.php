<?php

namespace Database\Factories;

use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    protected $model = NewsPost::class;

    public function definition(): array
    {
        $title = ucfirst(fake()->sentence(6));

        return [
            'title' => $title,
            'slug' => null,
            'excerpt' => fake()->sentence(16),
            'body' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'cover_image_path' => null,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false, 'published_at' => null]);
    }
}
