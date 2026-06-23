<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

/**
 * Imports the real doctor list from database/data/doctors.csv.
 * Idempotent: doctors are matched by name (safe to re-run locally and on prod).
 *
 * CSV columns: Doctor Name, Area, Phone, Address, Clinic Name, coordinates,
 *              Longitude, Latitude, Map (GeoJSON FeatureCollection).
 */
class DoctorImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/doctors.csv');

        if (! is_file($path)) {
            $this->command?->warn("Doctor CSV not found at {$path} — skipping.");

            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header

        $areas = [];   // name => Area (cache)
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                continue;
            }

            $areaName = trim((string) ($row[1] ?? ''));
            $areaId = null;

            if ($areaName !== '') {
                $area = $areas[$areaName] ??= Area::firstOrCreate(
                    ['name' => $areaName],
                    ['slug' => Area::uniqueSlug($areaName)],
                );
                $areaId = $area->id;
            }

            $latitude = is_numeric($row[7] ?? null) ? (float) $row[7] : null;
            $longitude = is_numeric($row[6] ?? null) ? (float) $row[6] : null;

            $location = null;
            $mapJson = trim((string) ($row[8] ?? ''));
            if ($mapJson !== '') {
                $decoded = json_decode($mapJson, true);
                $location = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            }
            if ($location === null && $latitude !== null && $longitude !== null) {
                $location = Doctor::pointGeoJson($latitude, $longitude);
            }

            Doctor::updateOrCreate(
                ['name' => $name],
                [
                    'area_id' => $areaId,
                    'phone' => trim((string) ($row[2] ?? '')) ?: null,
                    'address' => trim((string) ($row[3] ?? '')) ?: null,
                    'clinic_name' => trim((string) ($row[4] ?? '')) ?: null,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'location' => $location,
                    'status' => 'active',
                ],
            );

            $count++;
        }

        fclose($handle);

        $this->command?->info("Imported/updated {$count} doctors across ".count($areas).' areas.');
    }
}
