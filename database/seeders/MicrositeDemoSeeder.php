<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Microsite;
use App\Models\Review;
use App\Models\Showcase;
use Illuminate\Database\Seeder;

class MicrositeDemoSeeder extends Seeder
{
    /**
     * One ready-to-view demo doctor website: profile, showcases, and a mix of
     * approved + pending reviews so the moderation flow is visible in the panel.
     */
    public function run(): void
    {
        $doctor = Doctor::firstOrCreate(
            ['email' => 'demo.doctor@example.com'],
            [
                'name' => 'Jane Doe',
                'phone' => '9800000000',
                'specialty' => 'Cardiologist',
                'qualification' => 'MBBS, MD',
                'town' => 'Pune',
                'address' => '12 Health Street',
                'practice_since' => now()->subYears(8),
                'status' => 'active',
            ],
        );

        $microsite = Microsite::firstOrCreate(
            ['doctor_id' => $doctor->id],
            [
                'url' => Microsite::uniqueUrl($doctor->name),
                'is_active' => true,
                'design_settings' => ['bg_color' => '#24669B'],
            ],
        );

        if ($doctor->showcases()->doesntExist()) {
            Showcase::factory()->text()->create([
                'doctor_id' => $doctor->id,
                'title' => 'About the practice',
            ]);
        }

        if ($doctor->reviews()->doesntExist()) {
            Review::factory()->approved()->create([
                'doctor_id' => $doctor->id,
                'reviewer_name' => 'Satisfied Patient',
                'review_text' => 'Caring and thorough — highly recommended.',
                'rating' => 5,
            ]);
            Review::factory()->create([
                'doctor_id' => $doctor->id,
                'reviewer_name' => 'Awaiting Approval',
                'review_text' => 'This review is still pending moderation.',
                'rating' => 4,
            ]);
        }

        $this->command?->info('Demo microsite: '.$microsite->publicUrl());
    }
}
