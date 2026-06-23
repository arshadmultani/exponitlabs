<?php

use App\Models\Area;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\DoctorImportSeeder;

it('imports doctors and areas from the CSV with coordinates', function () {
    $this->seed(DoctorImportSeeder::class);

    expect(Doctor::count())->toBe(125);
    expect(Area::count())->toBeGreaterThan(0);

    $doctor = Doctor::whereNotNull('location')->first();
    expect($doctor->latitude)->not->toBeNull();
    expect($doctor->longitude)->not->toBeNull();
    expect(data_get($doctor->location, 'features.0.geometry.type'))->toBe('Point');
    expect($doctor->area)->not->toBeNull();
});

it('re-runs the importer without duplicating doctors', function () {
    $this->seed(DoctorImportSeeder::class);
    $this->seed(DoctorImportSeeder::class);

    expect(Doctor::count())->toBe(125);
});

it('derives lat/lng from the GeoJSON pin on save', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Pin Test',
        'location' => Doctor::pointGeoJson(19.5, 72.9),
    ]);

    expect((float) $doctor->latitude)->toBe(19.5);
    expect((float) $doctor->longitude)->toBe(72.9);
});

it('renders the doctor edit page with the map field', function () {
    $doctor = Doctor::create(['name' => 'Dr. Map']);

    $this->actingAs(User::factory()->create())
        ->get("/console/doctors/{$doctor->getKey()}/edit")
        ->assertOk()
        ->assertSee('locationMapField', false);
});

it('shows the doctors map widget on the list page', function () {
    Doctor::create([
        'name' => 'Dr. On Map',
        'location' => Doctor::pointGeoJson(19.5, 72.9),
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/console/doctors')
        ->assertOk()
        ->assertSee('doctorsMap', false)
        ->assertSee('Dr. On Map');
});

it('renders the doctor view page', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. View Me',
        'location' => Doctor::pointGeoJson(19.5, 72.9),
    ]);

    $this->actingAs(User::factory()->create())
        ->get("/console/doctors/{$doctor->getKey()}")
        ->assertOk()
        ->assertSee('Dr. View Me');
});
