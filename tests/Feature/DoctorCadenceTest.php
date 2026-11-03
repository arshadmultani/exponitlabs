<?php

use App\Filament\Pages\DoctorCadence;
use App\Models\DCR;
use App\Models\Doctor;
use App\Models\DoctorPotential;
use App\Models\User;
use App\Services\DoctorCadenceQueryService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure standard potential tiers exist
    $this->tierCore = DoctorPotential::firstOrCreate(['code' => 'core'], [
        'name' => 'Core',
        'target_visits_per_month' => 4,
        'color' => 'success',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->tierSerious = DoctorPotential::firstOrCreate(['code' => 'serious'], [
        'name' => 'Serious',
        'target_visits_per_month' => 2,
        'color' => 'info',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $this->tierNascent = DoctorPotential::firstOrCreate(['code' => 'nascent'], [
        'name' => 'Nascent',
        'target_visits_per_month' => 1,
        'color' => 'warning',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->tierUnresponsive = DoctorPotential::firstOrCreate(['code' => 'unresponsive'], [
        'name' => 'Unresponsive',
        'target_visits_per_month' => 0,
        'color' => 'gray',
        'sort_order' => 4,
        'is_active' => true,
    ]);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the doctor cadence and route planning page for authenticated users', function () {
    $this->get('/console/doctor-cadence')
        ->assertSuccessful()
        ->assertSee('Doctor Call Cadence & Route Planning');
});

it('computes correct cadence recency and visit counts across all-time and mtd horizons', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Cadence Test',
        'doctor_potential_id' => $this->tierCore->id,
        'status' => 'active',
    ]);

    // 1 call last month
    DCR::create([
        'doctor_id' => $doctor->id,
        'date' => now()->subMonth()->startOfMonth()->toDateString(),
        'remarks' => 'Past call',
    ]);

    // 2 calls this month (one 10 days ago, one today)
    DCR::create([
        'doctor_id' => $doctor->id,
        'date' => now()->startOfMonth()->toDateString(),
        'remarks' => 'Earlier this month',
    ]);

    DCR::create([
        'doctor_id' => $doctor->id,
        'date' => today()->toDateString(),
        'remarks' => 'Today visit',
    ]);

    $record = app(DoctorCadenceQueryService::class)->getEloquentQuery()
        ->where('doctors.id', $doctor->id)
        ->first();

    expect($record)->not->toBeNull()
        ->and((int) $record->total_visits_count)->toBe(3)
        ->and((int) $record->visits_this_month_count)->toBe(2)
        ->and($record->last_visit_date)->toBe(today()->toDateString());
});

it('displays doctors in cadence table with calculated metrics and badges', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Table Display',
        'doctor_potential_id' => $this->tierCore->id,
        'status' => 'active',
    ]);

    DCR::create([
        'doctor_id' => $doctor->id,
        'date' => today()->toDateString(),
    ]);

    Livewire::test(DoctorCadence::class)
        ->assertCanSeeTableRecords([$doctor])
        ->assertTableColumnStateSet('name', 'Dr. Table Display', $doctor)
        ->assertTableColumnStateSet('potential.name', 'Core', $doctor)
        ->assertTableColumnStateSet('total_visits_count', 1, $doctor)
        ->assertTableColumnStateSet('visits_this_month', '1 / 4 calls', $doctor)
        ->assertTableColumnStateSet('days_since_last_visit', 'Today', $doctor);
});

it('sorts never-visited doctors cleanly to the top when triaging overdue accounts', function () {
    $neverVisited = Doctor::factory()->create([
        'name' => 'Dr. Never Visited',
        'doctor_potential_id' => $this->tierNascent->id,
        'status' => 'active',
    ]);

    $recentlyVisited = Doctor::factory()->create([
        'name' => 'Dr. Recently Visited',
        'doctor_potential_id' => $this->tierCore->id,
        'status' => 'active',
    ]);

    DCR::create([
        'doctor_id' => $recentlyVisited->id,
        'date' => today()->toDateString(),
    ]);

    Livewire::test(DoctorCadence::class)
        ->sortTable('days_since_last_visit', 'desc')
        ->assertSeeInOrder([
            'Dr. Never Visited',
            'Dr. Recently Visited',
        ]);
});

it('partitions tabs strictly by commercial potential tier preserving unvisited accounts', function () {
    $activeCore = Doctor::factory()->create([
        'name' => 'Dr. Active Core',
        'doctor_potential_id' => $this->tierCore->id,
        'status' => 'active',
    ]);

    $unvisitedProspect = Doctor::factory()->create([
        'name' => 'Dr. Unvisited Prospect',
        'doctor_potential_id' => $this->tierNascent->id,
        'status' => 'active',
    ]);

    $unresponsiveDoc = Doctor::factory()->create([
        'name' => 'Dr. Dormant Unresponsive',
        'doctor_potential_id' => $this->tierUnresponsive->id,
        'status' => 'active',
    ]);

    // Active Cadence Tab (Default) sees active tiers (including unvisited) but excludes unresponsive
    Livewire::test(DoctorCadence::class)
        ->assertCanSeeTableRecords([$activeCore, $unvisitedProspect])
        ->assertCanNotSeeTableRecords([$unresponsiveDoc])
        // Switch to Unresponsive tab
        ->set('activeTab', 'unresponsive')
        ->assertCanSeeTableRecords([$unresponsiveDoc])
        ->assertCanNotSeeTableRecords([$activeCore, $unvisitedProspect])
        // Switch to All tab
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords([$activeCore, $unvisitedProspect, $unresponsiveDoc]);
});

it('filters doctors by day and shift respecting the open-availability fallback', function () {
    $wednesdayOnly = Doctor::factory()->create([
        'name' => 'Dr. Wednesday Only',
        'visiting_days' => ['wednesday'],
        'visiting_shifts' => ['morning'],
        'status' => 'active',
    ]);

    $fridayOnly = Doctor::factory()->create([
        'name' => 'Dr. Friday Only',
        'visiting_days' => ['friday'],
        'visiting_shifts' => ['evening'],
        'status' => 'active',
    ]);

    $openAvailability = Doctor::factory()->create([
        'name' => 'Dr. Open Door',
        'visiting_days' => null,
        'visiting_shifts' => null,
        'status' => 'active',
    ]);

    // Filter by Wednesday -> both Wednesday Only and Open Door appear
    Livewire::test(DoctorCadence::class)
        ->filterTable('visiting_day', 'wednesday')
        ->assertCanSeeTableRecords([$wednesdayOnly, $openAvailability])
        ->assertCanNotSeeTableRecords([$fridayOnly]);

    // Filter by Evening -> both Friday Only and Open Door appear
    Livewire::test(DoctorCadence::class)
        ->filterTable('visiting_shift', 'evening')
        ->assertCanSeeTableRecords([$fridayOnly, $openAvailability])
        ->assertCanNotSeeTableRecords([$wednesdayOnly]);
});

it('allows updating doctor potential and visiting schedule via slideover action', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Schedule Edit',
        'doctor_potential_id' => $this->tierNascent->id,
        'visiting_days' => null,
        'visiting_shifts' => null,
        'status' => 'active',
    ]);

    Livewire::test(DoctorCadence::class)
        ->callAction(TestAction::make('editSchedule')->table($doctor), [
            'doctor_potential_id' => $this->tierCore->id,
            'visiting_days' => ['monday', 'wednesday', 'friday'],
            'visiting_shifts' => ['morning'],
        ])
        ->assertNotified();

    $doctor->refresh();

    expect($doctor->doctor_potential_id)->toBe($this->tierCore->id)
        ->and($doctor->visiting_days)->toBe(['monday', 'wednesday', 'friday'])
        ->and($doctor->visiting_shifts)->toBe(['morning']);
});

it('logs a dcr call via table action, generates uuid, and recalculates recency', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Log Call',
        'doctor_potential_id' => $this->tierCore->id,
        'status' => 'active',
    ]);

    Livewire::test(DoctorCadence::class)
        ->callAction(TestAction::make('logVisit')->table($doctor), [
            'date' => today()->toDateString(),
            'remarks' => 'Discussed cardiology visual aid',
        ])
        ->assertNotified();

    $dcr = DCR::where('doctor_id', $doctor->id)->first();

    expect($dcr)->not->toBeNull()
        ->and($dcr->uuid)->not->toBeEmpty()
        ->and($dcr->date->toDateString())->toBe(today()->toDateString());

    // Attempting to log duplicate on same date triggers warning
    Livewire::test(DoctorCadence::class)
        ->callAction(TestAction::make('logVisit')->table($doctor), [
            'date' => today()->toDateString(),
            'remarks' => 'Second attempt',
        ])
        ->assertNotified('A visit has already been logged for Dr. Log Call on '.today()->toDateString());

    expect(DCR::where('doctor_id', $doctor->id)->count())->toBe(1);
});

it('synchronizes potential and schedule to mobile master data and defaults offline doctors to nascent', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Mobile Sync',
        'doctor_potential_id' => $this->tierSerious->id,
        'visiting_days' => ['tuesday', 'thursday'],
        'visiting_shifts' => ['morning', 'evening'],
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/v1/sync/master-data')
        ->assertSuccessful();

    $doctorPayload = collect($response->json('doctors'))
        ->firstWhere('id', $doctor->id);

    expect($doctorPayload)->not->toBeNull()
        ->and($doctorPayload['potential']['code'])->toBe('serious')
        ->and($doctorPayload['potential']['target_visits_per_month'])->toBe(2)
        ->and($doctorPayload['visiting_days'])->toBe(['tuesday', 'thursday'])
        ->and($doctorPayload['visiting_shifts'])->toBe(['morning', 'evening']);

    // Post newly created offline doctor without potential tier -> auto defaults to Nascent
    $offlineUuid = (string) Str::uuid();

    $this->postJson('/api/v1/sync/doctors-batch', [
        'doctors' => [
            [
                'uuid' => $offlineUuid,
                'name' => 'Dr. Offline Created',
                'clinic_name' => 'City Clinic',
                'town' => 'Mumbai',
            ],
        ],
    ])->assertSuccessful();

    $createdDoctor = Doctor::where('uuid', $offlineUuid)->first();

    expect($createdDoctor)->not->toBeNull()
        ->and($createdDoctor->doctor_potential_id)->toBe($this->tierNascent->id);
});
