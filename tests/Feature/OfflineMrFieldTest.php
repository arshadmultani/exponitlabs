<?php

use App\Models\DCR;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders ELOS field app web routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/elos/dcr')->assertStatus(200);
    $this->get('/elos/doctors')->assertStatus(200);
    $this->get('/elos/doctors/create')->assertStatus(200);
    $this->get('/elos/doctors/'.Str::uuid())->assertStatus(200);
    $this->get('/elos/presentation')->assertStatus(200);
});

it('renders ELOS 16:9 presentation stage with detailing HUD and telestrator', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Rajesh Sharma',
        'specialty' => 'Cardiologist',
    ]);

    $response = $this->get('/elos/presentation?doctor='.$doctor->uuid);

    $response->assertOk()
        ->assertSee('CardioGuard')
        ->assertSee('Dr. Rajesh Sharma')
        ->assertSee('telestrator-canvas')
        ->assertSee('presentationApp');
});

it('redirects unauthenticated users trying to access elos presentation', function () {
    $this->get('/elos/presentation')->assertRedirect('/elos/login');
});

it('renders doctor show page with doctor details and past DCRs in ELOS field app', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $doctor = Doctor::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Dr. Meera Patel',
        'specialty' => 'Pediatrician',
    ]);
    DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'remarks' => 'Routine monthly visit notes',
    ]);

    $response = $this->get('/elos/doctors/'.$doctor->uuid);
    $response->assertOk()
        ->assertSee('Dr. Meera Patel')
        ->assertSee('Routine monthly visit notes');
});

it('downloads master data payload via API', function () {
    Doctor::factory()->create(['name' => 'Dr. Alpha', 'status' => 'active']);
    Product::factory()->create(['name' => 'Tablet X']);
    PromotionalInput::create(['name' => 'Visual Chart', 'type' => 'Gift']);

    $response = $this->getJson('/api/v1/sync/master-data');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'server_time',
            'doctors',
            'products',
            'promotional_inputs',
            'visit_history',
        ]);
});

it('syncs batch of offline created doctors', function () {
    $doctorUuid = (string) Str::uuid();

    $payload = [
        'doctors' => [
            [
                'uuid' => $doctorUuid,
                'name' => 'Dr. Beta Offline',
                'specialty' => 'Dermatology',
                'phone' => '+919999888877',
                'town' => 'Pune',
                'clinic_name' => 'Beta Skin Clinic',
                'address' => 'Station Road',
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/sync/doctors-batch', $payload);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('doctors', [
        'uuid' => $doctorUuid,
        'name' => 'Dr. Beta Offline',
        'specialty' => 'Dermatology',
    ]);
});

it('syncs batch of offline created DCR entries', function () {
    $doctor = Doctor::factory()->create();
    $product = Product::factory()->create();
    $input = PromotionalInput::create(['name' => 'Pen', 'type' => 'Gift']);

    $dcrUuid = (string) Str::uuid();

    $payload = [
        'dcrs' => [
            [
                'client_uuid' => $dcrUuid,
                'date' => '2026-08-04',
                'doctor_id' => $doctor->id,
                'doctor_uuid' => $doctor->uuid,
                'remarks' => 'Great product discussion.',
                'products' => [
                    ['product_id' => $product->id, 'quantity' => 5],
                ],
                'promotional_inputs' => [
                    ['promotional_input_id' => $input->id, 'quantity' => 1],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/sync/dcr-batch', $payload);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'synced_uuids' => [$dcrUuid]]);

    $this->assertDatabaseHas('d_c_r_s', [
        'uuid' => $dcrUuid,
        'doctor_id' => $doctor->id,
        'remarks' => 'Great product discussion.',
    ]);

    $this->assertDatabaseHas('dcr_products', [
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $this->assertDatabaseHas('dcr_promotional_inputs', [
        'promotional_input_id' => $input->id,
        'quantity' => 1,
    ]);
});
