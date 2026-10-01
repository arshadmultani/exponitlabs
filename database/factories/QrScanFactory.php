<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\QrScan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrScan>
 */
class QrScanFactory extends Factory
{
    protected $model = QrScan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'qr_code_id' => QrCode::factory(),
            'ip_address' => fake()->ipv4(),
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'country_code' => fake()->countryCode(),
            'country_name' => fake()->country(),
            'region' => fake()->state(),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'device_type' => fake()->randomElement(['mobile', 'tablet', 'desktop']),
            'device_brand' => fake()->randomElement(['Apple', 'Samsung', 'Google', null]),
            'device_model' => fake()->randomElement(['iPhone', 'Galaxy S23', 'Pixel 8', null]),
            'platform' => fake()->randomElement(['iOS', 'Android', 'Windows', 'macOS']),
            'platform_version' => '17.0',
            'browser' => fake()->randomElement(['Safari', 'Chrome', 'Firefox']),
            'browser_version' => '17.0',
            'is_unique' => true,
            'referrer' => fake()->url(),
            'user_agent' => fake()->userAgent(),
            'scanned_at' => now(),
        ];
    }
}
