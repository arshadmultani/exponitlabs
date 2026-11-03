<?php

use App\Filament\Resources\DoctorPotentials\Pages\ManageDoctorPotentials;
use App\Models\DoctorPotential;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the doctor potentials management page and lists tiers', function () {
    $tier = DoctorPotential::create([
        'name' => 'High Value',
        'code' => 'high_value',
        'target_visits_per_month' => 5,
        'color' => 'success',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Livewire::test(ManageDoctorPotentials::class)
        ->assertCanSeeTableRecords([$tier])
        ->assertTableColumnStateSet('name', 'High Value', $tier)
        ->assertTableColumnStateSet('code', 'high_value', $tier)
        ->assertTableColumnStateSet('target_visits_per_month', '5 calls / month', $tier);
});

it('creates a new doctor potential tier via modal action', function () {
    Livewire::test(ManageDoctorPotentials::class)
        ->callAction(CreateAction::class, [
            'name' => 'VIP Key Account',
            'code' => 'vip_account',
            'target_visits_per_month' => 6,
            'color' => 'success',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(DoctorPotential::class, [
        'name' => 'VIP Key Account',
        'code' => 'vip_account',
        'target_visits_per_month' => 6,
    ]);
});

it('updates an existing doctor potential tier', function () {
    $tier = DoctorPotential::create([
        'name' => 'Initial Tier',
        'code' => 'initial_tier',
        'target_visits_per_month' => 2,
        'color' => 'info',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    Livewire::test(ManageDoctorPotentials::class)
        ->callAction(TestAction::make(EditAction::class)->table($tier), [
            'name' => 'Updated Tier Name',
            'target_visits_per_month' => 3,
        ])
        ->assertHasNoActionErrors();

    $tier->refresh();
    expect($tier->name)->toBe('Updated Tier Name')
        ->and($tier->target_visits_per_month)->toBe(3);
});
