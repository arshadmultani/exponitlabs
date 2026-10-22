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
        ->set('mountedActions.0.data.products', [$product->id])
        ->set('mountedActions.0.data.inputs', [$input->id])
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
        'quantity' => 1,
    ]);

    $this->assertDatabaseHas(DCRPromotionalInput::class, [
        'dcr_id' => $dcr->id,
        'promotional_input_id' => $input->id,
        'quantity' => 1,
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

test('it creates a dcr with multiple sample products and promotional inputs selected via multi-select', function () {
    $user = User::factory()->create();
    $doctor = Doctor::factory()->create();
    $p1 = Product::factory()->create(['name' => 'Med A']);
    $p2 = Product::factory()->create(['name' => 'Med B']);
    $i1 = PromotionalInput::factory()->create(['name' => 'Calendar']);
    $i2 = PromotionalInput::factory()->create(['name' => 'Brochure']);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->mountAction('create')
        ->set('mountedActions.0.data.date', '2026-10-22')
        ->set('mountedActions.0.data.doctor_id', $doctor->id)
        ->set('mountedActions.0.data.sample_given', true)
        ->set('mountedActions.0.data.input_given', true)
        ->set('mountedActions.0.data.products', [$p1->id, $p2->id])
        ->set('mountedActions.0.data.inputs', [$i1->id, $i2->id])
        ->set('mountedActions.0.data.remarks', 'Multi-select test visit')
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $dcr = DCR::where('remarks', 'Multi-select test visit')->first();
    expect($dcr)->not->toBeNull();
    expect($dcr->sampleProducts)->toHaveCount(2);
    expect($dcr->promotionalInputs)->toHaveCount(2);
    expect($dcr->sampleProducts->pluck('product_id')->all())->toEqualCanonicalizing([$p1->id, $p2->id]);
    expect($dcr->promotionalInputs->pluck('promotional_input_id')->all())->toEqualCanonicalizing([$i1->id, $i2->id]);
});

test('it can edit a dcr and update its sample products and promotional inputs', function () {
    $user = User::factory()->create();
    $doctor = Doctor::factory()->create();
    $p1 = Product::factory()->create(['name' => 'Old Product']);
    $p2 = Product::factory()->create(['name' => 'New Product']);

    $dcr = DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-10-22',
        'remarks' => 'Before edit',
    ]);
    $dcr->sampleProducts()->create(['product_id' => $p1->id, 'quantity' => 1]);

    $this->actingAs($user);

    Livewire::test(ManageDCRS::class)
        ->callTableAction('edit', $dcr, data: [
            'sample_given' => true,
            'products' => [$p2->id],
            'remarks' => 'After edit',
        ])
        ->assertHasNoTableActionErrors();

    $dcr->refresh();
    expect($dcr->remarks)->toBe('After edit');
    expect($dcr->sampleProducts)->toHaveCount(1);
    expect($dcr->sampleProducts->first()->product_id)->toBe($p2->id);
});
