<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorPotential;
use Illuminate\Database\Seeder;

class DoctorPotentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DoctorPotential::firstOrCreate(['code' => 'core'], [
            'name' => 'Core',
            'target_visits_per_month' => 4,
            'color' => 'success',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DoctorPotential::firstOrCreate(['code' => 'serious'], [
            'name' => 'Serious',
            'target_visits_per_month' => 2,
            'color' => 'info',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $nascent = DoctorPotential::firstOrCreate(['code' => 'nascent'], [
            'name' => 'Nascent',
            'target_visits_per_month' => 1,
            'color' => 'warning',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        DoctorPotential::firstOrCreate(['code' => 'unresponsive'], [
            'name' => 'Unresponsive',
            'target_visits_per_month' => 0,
            'color' => 'gray',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        Doctor::whereNull('doctor_potential_id')->update([
            'doctor_potential_id' => $nascent->id,
        ]);
    }
}
