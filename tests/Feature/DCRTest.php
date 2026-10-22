<?php

use App\Filament\Resources\DCRS\Pages\ManageDCRS;
use App\Models\Area;
use App\Models\DCR;
use App\Models\DCRProduct;
use App\Models\DCRPromotionalInput;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('it creates a dcr along with sample products and promotional inputs', function () {
    $user = User::factory()->create();
    $doctor = Doctor::factory()->create();
    $product = Product::factory()->create();
    $input = PromotionalInput::factory()->create();

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->mountAction('create')
        ->set('mountedActions.0.data.date', now()->toDateString())
        ->set('mountedActions.0.data.doctor_id', $doctor->id)
        ->set('mountedActions.0.data.sample_given', true)
        ->set('mountedActions.0.data.input_given', true)
        ->set('mountedActions.0.data.products', [$product->id => 5])
        ->set('mountedActions.0.data.inputs', [$input->id => 3])
        ->set('mountedActions.0.data.remarks', 'Test visit')
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(DCR::class, [
        'doctor_id' => $doctor->id,
        'remarks' => 'Test visit',
    ]);

    $dcr = DCR::first();

    $this->assertDatabaseHas(DCRProduct::class, [
        'dcr_id' => $dcr->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $this->assertDatabaseHas(DCRPromotionalInput::class, [
        'dcr_id' => $dcr->id,
        'promotional_input_id' => $input->id,
        'quantity' => 3,
    ]);
});

test('it displays dcr details in view action infolist', function () {
    $user = User::factory()->create();
    $dcr = DCR::factory()->create(['remarks' => 'Detailed visit notes']);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->mountTableAction('view', $dcr)
        ->assertHasNoTableActionErrors();
});

test('it displays doctor area and town below doctor name in table and select options', function () {
    $user = User::factory()->create();
    $area = Area::create(['name' => 'Downtown Hub']);
    $doctorWithBoth = Doctor::factory()->create([
        'name' => 'Dr. Jane Doe',
        'area_id' => $area->id,
        'town' => 'Metropolis',
    ]);
    $doctorWithTownOnly = Doctor::factory()->create([
        'name' => 'Dr. John Smith',
        'area_id' => null,
        'town' => 'Gotham',
    ]);
    $dcr1 = DCR::factory()->create(['doctor_id' => $doctorWithBoth->id]);
    $dcr2 = DCR::factory()->create(['doctor_id' => $doctorWithTownOnly->id]);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->assertCanSeeTableRecords([$dcr1, $dcr2])
        ->assertSee('Downtown Hub, Metropolis')
        ->assertSee('Gotham')
        ->mountAction('create')
        ->assertSee('Downtown Hub, Metropolis')
        ->assertSee('Gotham');
});

test('it groups dcr table records by date and displays doctor call count', function () {
    $user = User::factory()->create();
    $doc1 = Doctor::factory()->create(['name' => 'Dr. Alpha']);
    $doc2 = Doctor::factory()->create(['name' => 'Dr. Beta']);
    $doc3 = Doctor::factory()->create(['name' => 'Dr. Gamma']);

    $todayDcr1 = DCR::factory()->create([
        'doctor_id' => $doc1->id,
        'date' => '2026-10-22',
    ]);
    $todayDcr2 = DCR::factory()->create([
        'doctor_id' => $doc2->id,
        'date' => '2026-10-22',
    ]);
    $yesterdayDcr = DCR::factory()->create([
        'doctor_id' => $doc3->id,
        'date' => '2026-10-21',
    ]);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->assertCanSeeTableRecords([$todayDcr1, $todayDcr2, $yesterdayDcr])
        ->assertSee('2 doctor calls')
        ->assertSee('1 doctor call');
});

test('it updates doctor call count in group header when search filter is applied', function () {
    $user = User::factory()->create();
    $doc1 = Doctor::factory()->create(['name' => 'Dr. UniqueAlpha']);
    $doc2 = Doctor::factory()->create(['name' => 'Dr. CommonBeta']);

    DCR::factory()->create([
        'doctor_id' => $doc1->id,
        'date' => '2026-10-22',
    ]);
    DCR::factory()->create([
        'doctor_id' => $doc2->id,
        'date' => '2026-10-22',
    ]);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->assertSee('2 doctor calls')
        ->searchTable('UniqueAlpha')
        ->assertSee('1 doctor call')
        ->assertDontSee('2 doctor calls');
});
