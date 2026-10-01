<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QrCode>
 */
class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(3, true),
            'code' => Str::lower(fake()->unique()->lexify('???????')),
            'destination_url' => fake()->url(),
            'ios_url' => null,
            'android_url' => null,
            'fallback_url' => null,
            'is_active' => true,
            'expires_at' => null,
            'max_scans' => null,
            'total_scans' => 0,
            'unique_scans' => 0,
            'last_scanned_at' => null,
            'design' => QrCode::defaultDesign(),
            'user_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
