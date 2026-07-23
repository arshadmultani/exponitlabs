<?php

use App\Filament\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Resources\Doctors\Pages\EditDoctor;
use App\Models\Doctor;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('creates a doctor with location data', function () {
    $geoJson = Doctor::pointGeoJson(19.0760, 72.8777);

    Livewire::test(CreateDoctor::class)
        ->fillForm([
            'name' => 'Dr. Test Location',
            'status' => 'active',
            'location' => $geoJson,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Doctor::class, [
        'name' => 'Dr. Test Location',
    ]);

    $doctor = Doctor::where('name', 'Dr. Test Location')->first();
    expect($doctor->location)->not->toBeNull()
        ->and((float) $doctor->latitude)->toBe(19.076)
        ->and((float) $doctor->longitude)->toBe(72.8777);
});

it('creates a doctor with high precision lat long coordinates', function () {
    $lat = 19.40152145075214;
    $lng = 72.84219908221823;
    $geoJson = Doctor::pointGeoJson($lat, $lng);

    Livewire::test(CreateDoctor::class)
        ->fillForm([
            'name' => 'Dr. High Precision',
            'status' => 'active',
            'location' => $geoJson,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $doctor = Doctor::where('name', 'Dr. High Precision')->first();
    expect($doctor->location)->not->toBeNull()
        ->and(round((float) $doctor->latitude, 4))->toBe(round($lat, 4))
        ->and(round((float) $doctor->longitude, 4))->toBe(round($lng, 4));
});

it('edits a doctor location data', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Edit Location',
        'status' => 'active',
        'location' => Doctor::pointGeoJson(19.0, 72.0),
    ]);

    $newGeoJson = Doctor::pointGeoJson(20.0, 73.0);

    Livewire::test(EditDoctor::class, ['record' => $doctor->id])
        ->fillForm([
            'location' => $newGeoJson,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $doctor->refresh();
    expect((float) $doctor->latitude)->toBe(20.0)
        ->and((float) $doctor->longitude)->toBe(73.0);
});
