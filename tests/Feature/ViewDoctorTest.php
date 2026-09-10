<?php

use App\Filament\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Resources\Doctors\Pages\ViewDoctor;
use App\Filament\Resources\Doctors\RelationManagers\DcrsRelationManager;
use App\Models\Area;
use App\Models\DCR;
use App\Models\DCRProduct;
use App\Models\DCRPromotionalInput;
use App\Models\Doctor;
use App\Models\Headquarter;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the doctor view page with all doctor details and visit summary', function () {
    $hq = Headquarter::create(['name' => 'Mumbai HQ', 'slug' => 'mumbai-hq']);
    $area = Area::create(['name' => 'Bandra West', 'headquarter_id' => $hq->id]);

    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Aditi Sharma',
        'email' => 'aditi.sharma@example.com',
        'phone' => '9820123456',
        'specialty' => 'Cardiologist',
        'qualification' => 'MBBS, MD (Cardiology)',
        'clinic_name' => 'Heart Care Clinic',
        'town' => 'Bandra',
        'address' => '102 Hill Road, Bandra West',
        'area_id' => $area->id,
        'status' => 'active',
        'latitude' => 19.0596,
        'longitude' => 72.8295,
        'practice_since' => '2015-06-01',
    ]);

    $dcr1 = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-08-01',
        'remarks' => 'First introductory call',
    ]);
    $dcr2 = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-08-15',
        'remarks' => 'Follow-up discussion on efficacy',
    ]);

    $product = Product::factory()->create(['name' => 'CardioSafe 50mg']);
    DCRProduct::create([
        'dcr_id' => $dcr1->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    Livewire::test(ViewDoctor::class, ['record' => $doctor->id])
        ->assertSee('Dr. Aditi Sharma')
        ->assertSee('aditi.sharma@example.com')
        ->assertSee('9820123456')
        ->assertSee('Cardiologist')
        ->assertSee('MBBS, MD (Cardiology)')
        ->assertSee('Heart Care Clinic')
        ->assertSee('Bandra')
        ->assertSee('102 Hill Road, Bandra West')
        ->assertSee('Bandra West')
        ->assertSee('Mumbai HQ')
        ->assertSee('19.0596')
        ->assertSee('72.8295')
        ->assertSee('Active')
        ->assertSee('Total DCRs / Visits')
        ->assertSee('2')
        ->assertSee('Total Samples Distributed')
        ->assertSee('4');
});

it('displays all of that doctors dcrs done till date in the dcrs relation manager', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Rajesh Patel']);
    $otherDoctor = Doctor::factory()->create(['name' => 'Dr. Other Person']);

    $product1 = Product::factory()->create(['name' => 'NeuroPlus']);
    $input1 = PromotionalInput::factory()->create(['name' => 'Desk Calendar']);

    $dcr1 = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-07-10',
        'remarks' => 'Discussed summer campaign',
    ]);
    DCRProduct::create([
        'dcr_id' => $dcr1->id,
        'product_id' => $product1->id,
        'quantity' => 3,
    ]);
    DCRPromotionalInput::create([
        'dcr_id' => $dcr1->id,
        'promotional_input_id' => $input1->id,
        'quantity' => 2,
    ]);

    $dcr2 = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-08-20',
        'remarks' => 'Monthly regular visit',
    ]);

    $otherDcr = DCR::factory()->create([
        'doctor_id' => $otherDoctor->id,
        'date' => '2026-08-21',
        'remarks' => 'Unrelated visit to other doctor',
    ]);

    Livewire::test(DcrsRelationManager::class, [
        'ownerRecord' => $doctor,
        'pageClass' => ViewDoctor::class,
    ])
        ->assertCanSeeTableRecords([$dcr1, $dcr2])
        ->assertCanNotSeeTableRecords([$otherDcr])
        ->assertSee('Discussed summer campaign')
        ->assertSee('Monthly regular visit')
        ->assertSee('NeuroPlus (x3)')
        ->assertSee('Desk Calendar (x2)')
        ->assertDontSee('Unrelated visit to other doctor');
});

it('can view dcr details in modal from relation manager', function () {
    $doctor = Doctor::factory()->create();
    $dcr = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'remarks' => 'Deep dive visit discussion notes',
    ]);

    Livewire::test(DcrsRelationManager::class, [
        'ownerRecord' => $doctor,
        'pageClass' => ViewDoctor::class,
    ])
        ->mountTableAction('view', $dcr)
        ->assertHasNoTableActionErrors()
        ->assertSee('Deep dive visit discussion notes');
});

it('can log a new dcr for the doctor through the relation manager', function () {
    $doctor = Doctor::factory()->create();
    $product = Product::factory()->create(['name' => 'Sample Syrup']);
    $input = PromotionalInput::factory()->create(['name' => 'Branded Pen']);

    Livewire::test(DcrsRelationManager::class, [
        'ownerRecord' => $doctor,
        'pageClass' => ViewDoctor::class,
    ])
        ->callTableAction('create', data: [
            'date' => '2026-09-01',
            'sample_given' => true,
            'input_given' => true,
            'products' => [$product->id => 6],
            'inputs' => [$input->id => 10],
            'remarks' => 'Created from doctor view page',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(DCR::class, [
        'doctor_id' => $doctor->id,
        'remarks' => 'Created from doctor view page',
    ]);

    $newDcr = DCR::where('remarks', 'Created from doctor view page')->first();

    $this->assertDatabaseHas(DCRProduct::class, [
        'dcr_id' => $newDcr->id,
        'product_id' => $product->id,
        'quantity' => 6,
    ]);

    $this->assertDatabaseHas(DCRPromotionalInput::class, [
        'dcr_id' => $newDcr->id,
        'promotional_input_id' => $input->id,
        'quantity' => 10,
    ]);
});

it('renders view action and dcr count column in doctors list table', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. List Check']);
    DCR::factory()->count(3)->create(['doctor_id' => $doctor->id]);

    Livewire::test(ListDoctors::class)
        ->assertCanSeeTableRecords([$doctor])
        ->assertTableActionExists('view', record: $doctor)
        ->assertSee('3');
});
