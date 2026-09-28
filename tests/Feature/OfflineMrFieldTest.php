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
    Doctor::factory()->create([
        'name' => 'Dr. Alpha',
        'status' => 'active',
        'phone' => '+919876543210',
        'profile_photo' => 'doctors/alpha.jpg',
    ]);
    Product::factory()->create([
        'name' => 'Tablet X',
        'composition' => 'Molecule A 500mg',
        'strength' => '500mg',
        'packaging' => '10x10 Tablets',
        'image_path' => 'products/tablet-x.jpg',
    ]);
    PromotionalInput::create(['name' => 'Visual Chart', 'type' => 'Gift']);

    $response = $this->getJson('/api/v1/sync/master-data');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'server_time',
            'doctors' => [
                '*' => ['id', 'uuid', 'name', 'phone', 'profile_photo', 'profile_photo_url'],
            ],
            'products' => [
                '*' => ['id', 'name', 'composition', 'strength', 'packaging', 'image_path', 'image_url'],
            ],
            'promotional_inputs',
            'visit_history',
        ]);
});

it('renders call reminder modal and quick actions across MR field portal', function () {
    $user = User::factory()->create(['name' => 'Jane Representative']);
    $this->actingAs($user);

    $doctor = Doctor::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Dr. Suresh Verma',
        'phone' => '+919876543210',
    ]);

    // 1. DCR entry page includes Call Reminder modal
    $dcrResponse = $this->get('/elos/dcr');
    $dcrResponse->assertOk()
        ->assertSee('callReminderApp')
        ->assertSee('reminderCanvas')
        ->assertSee('WhatsApp Call Reminder')
        ->assertSee('Jane Representative');

    // 2. Doctor detail page includes WhatsApp Reminder action button
    $docResponse = $this->get('/elos/doctors/'.$doctor->uuid);
    $docResponse->assertOk()
        ->assertSee('WhatsApp Reminder')
        ->assertSee('openReminder()');

    // 3. DCR history logs page includes WhatsApp Reminder action button
    $historyResponse = $this->get('/elos/dcrs');
    $historyResponse->assertOk()
        ->assertSee('Reminder')
        ->assertSee('openReminderForDcr(dcr)');
});

it('normalizes various doctor phone number formats to valid WhatsApp numbers with 91 prefix', function () {
    $cases = [
        '9876543210' => '919876543210',
        '09876543210' => '919876543210',
        '919876543210' => '919876543210',
        '+91 98765 43210' => '919876543210',
        '+91-98765-43210' => '919876543210',
        '00919876543210' => '919876543210',
        '0919876543210' => '919876543210',
        '+91 (98765) 43210' => '919876543210',
    ];

    foreach ($cases as $raw => $expected) {
        $doctor = new Doctor(['phone' => $raw]);
        expect($doctor->whatsapp_number)->toBe($expected);
    }

    $emptyDoctor = new Doctor(['phone' => null]);
    expect($emptyDoctor->whatsapp_number)->toBeNull();
});

it('renders call reminder studio with 2 delivery modes and laptop clipboard paste guide', function () {
    $user = User::factory()->create(['name' => 'Jane Representative']);
    $this->actingAs($user);

    $response = $this->get('/elos/dcr');
    $response->assertOk()
        // Header & State
        ->assertSee('WhatsApp Call Reminder')
        ->assertSee('callReminderApp()')
        ->assertSee('reminderCanvas')
        // Phone input with +91 country prefix badge & validation
        ->assertSee('🇮🇳 +91')
        ->assertSee('+91 Verified')
        ->assertSee('10 Digits Required')
        // 2 Delivery Modes
        ->assertSee('WhatsApp Delivery Mode')
        ->assertSee('Card + Text Caption')
        ->assertSee('Sends Both')
        ->assertSee('Direct Chat (Text Only)')
        // Laptop Clipboard & Keyboard Shortcut Guide
        ->assertSee('Card Image Copied to Clipboard!')
        ->assertSee('Ctrl + V')
        ->assertSee('Cmd + V')
        ->assertSee('Re-Copy Card')
        // 3-Button Canvas Utility Bar
        ->assertSee('Download')
        ->assertSee('Copy Card')
        ->assertSee('Copy Text')
        // Dispatch Actions
        ->assertSee('Send Card + Text on WhatsApp')
        ->assertSee("Open Doctor's Chat Directly", false);
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

it('renders call reminder studio with logged DCR date select dropdown and reminder status indicators', function () {
    $user = User::factory()->create(['name' => 'Jane Representative']);
    $this->actingAs($user);

    $response = $this->get('/elos/dcr');
    $response->assertOk()
        ->assertSee('Logged DCR Date')
        ->assertSee('Include Date')
        ->assertSee('None / General (No visit date)')
        ->assertSee('General Mode: Visit date is omitted')
        ->assertSee('onVisitDateSelect($event.target.value)', false)
        ->assertSee('No Calls Yet')
        ->assertSee('Sent on')
        ->assertSee('No reminder sent yet for this visit');
});

it('renders DCR history page with reminder status filter pills and sent badges', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $doctor = Doctor::factory()->create(['name' => 'Dr. Rajiv Malhotra']);
    DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'remarks' => 'Cardiac consultation notes',
        'reminder_sent_at' => now(),
    ]);

    $response = $this->get('/elos/dcrs');
    $response->assertOk()
        ->assertSee('Reminders:')
        ->assertSee("reminderFilter = 'all'", false)
        ->assertSee("reminderFilter = 'sent'", false)
        ->assertSee("reminderFilter = 'pending'", false)
        ->assertSee('Reminder Pending')
        ->assertSee('Send Reminder')
        ->assertSee('formatDate(dcr.date)', false);
});

it('renders doctor profile page with reminder status in header and visit history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $doctor = Doctor::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Dr. Ananya Roy',
        'specialty' => 'Neurologist',
    ]);

    DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'remarks' => 'Migraine follow-up',
        'reminder_sent_at' => now(),
    ]);

    $response = $this->get('/elos/doctors/'.$doctor->uuid);
    $response->assertOk()
        ->assertSee('Last Reminder:')
        ->assertSee('Reminder Sent')
        ->assertSee('formatDate(dcr.date)', false)
        ->assertSee('formatDate(v.date)', false);
});

it('syncs batch of reminder logs via API and updates DCR reminder_sent_at', function () {
    $doctor = Doctor::factory()->create();
    $dcrUuid = (string) Str::uuid();
    $dcr = DCR::create([
        'uuid' => $dcrUuid,
        'doctor_id' => $doctor->id,
        'date' => '2026-09-28',
        'remarks' => 'Discussion on antibiotics',
    ]);

    expect($dcr->reminder_sent_at)->toBeNull();

    $sentAt = now()->toIso8601String();
    $payload = [
        'reminders' => [
            [
                'dcr_client_uuid' => $dcrUuid,
                'doctor_uuid' => $doctor->uuid,
                'visit_date' => '2026-09-28',
                'sent_at' => $sentAt,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/sync/reminders-batch', $payload);
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'updated_count' => 1,
        ]);

    $dcr->refresh();
    expect($dcr->reminder_sent_at)->not->toBeNull();

    // Verify masterData API also returns reminder_sent_at
    $masterDataResponse = $this->getJson('/api/v1/sync/master-data');
    $masterDataResponse->assertOk()
        ->assertJsonFragment([
            'uuid' => $dcrUuid,
            'reminder_sent_at' => $dcr->reminder_sent_at->toIso8601String(),
        ]);
});
