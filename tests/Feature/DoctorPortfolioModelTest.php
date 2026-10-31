<?php

use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('verifies config exponit default visit cost', function () {
    expect(config('exponit.default_visit_cost'))->toBe(250.0);
});

it('verifies promotional input model unit_cost casting', function () {
    $promo = PromotionalInput::create([
        'name' => 'Visual Aid Kit',
        'unit_cost' => 125.75,
        'is_active' => true,
    ]);

    $promo->refresh();
    expect($promo->unit_cost)->toBe('125.75')
        ->and((float) $promo->unit_cost)->toBe(125.75)
        ->and($promo->is_active)->toBeTrue();
});

it('verifies product model sample_cost casting and fallback', function () {
    $product = Product::factory()->create([
        'unit_cost' => 50.00,
        'sample_cost' => 35.50,
    ]);

    $product->refresh();
    expect($product->unit_cost)->toBe('50.00')
        ->and($product->sample_cost)->toBe('35.50')
        ->and((float) $product->sample_cost)->toBe(35.50);
});

it('verifies doctor model visit_cost_override and revenues relationship', function () {
    $doctor = Doctor::factory()->create([
        'visit_cost_override' => 350.00,
    ]);

    $doctor->refresh();
    expect($doctor->visit_cost_override)->toBe('350.00')
        ->and((float) $doctor->visit_cost_override)->toBe(350.00);

    $revenue = DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'amount' => 12000.00,
    ]);

    expect($doctor->revenues)->toHaveCount(1)
        ->and($doctor->revenues->first()->id)->toBe($revenue->id)
        ->and((float) $doctor->revenues->first()->amount)->toBe(12000.00);
});

it('verifies doctor revenue factory and relationships', function () {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create();

    $revenue = DoctorRevenue::factory()
        ->csvImported()
        ->forPeriod('2026-11-15')
        ->create([
            'doctor_id' => $doctor->id,
            'created_by_user_id' => $user->id,
            'amount' => 8450.50,
            'notes' => 'Apollo Hospital retail sales',
        ]);

    $revenue->refresh();

    expect($revenue->source)->toBe('csv_import')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-11-01')
        ->and((float) $revenue->amount)->toBe(8450.50)
        ->and($revenue->doctor->id)->toBe($doctor->id)
        ->and($revenue->creator->id)->toBe($user->id);
});
