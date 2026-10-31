<?php

use App\Models\Doctor;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('verifies phase 1 schema columns and defaults are applied', function () {
    expect(Schema::hasColumn('promotional_inputs', 'unit_cost'))->toBeTrue()
        ->and(Schema::hasColumn('products', 'sample_cost'))->toBeTrue()
        ->and(Schema::hasColumn('doctors', 'visit_cost_override'))->toBeTrue()
        ->and(Schema::hasColumn('dcr_products', 'unit_cost_snapshot'))->toBeTrue()
        ->and(Schema::hasColumn('dcr_promotional_inputs', 'unit_cost_snapshot'))->toBeTrue()
        ->and(Schema::hasTable('doctor_revenues'))->toBeTrue();
});

it('verifies promotional_inputs unit_cost default is 0.00 and products sample_cost is nullable', function () {
    $promo = PromotionalInput::create(['name' => 'Visual Aid']);
    $promo->refresh();
    expect((float) $promo->unit_cost)->toBe(0.0);

    $product = Product::factory()->create(['sample_cost' => null]);
    $product->refresh();
    expect($product->sample_cost)->toBeNull();

    $productWithSample = Product::factory()->create(['sample_cost' => 45.50]);
    $productWithSample->refresh();
    expect((float) $productWithSample->sample_cost)->toBe(45.50);
});

it('allows multiple revenue records per doctor per period without collision', function () {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create();

    $id1 = DB::table('doctor_revenues')->insertGetId([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-10-01',
        'amount' => 4500.00,
        'source' => 'csv_import',
        'notes' => 'Apollo Pharmacy stockist audit',
        'created_by_user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $id2 = DB::table('doctor_revenues')->insertGetId([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-10-01',
        'amount' => 3200.00,
        'source' => 'csv_import',
        'notes' => 'MedPlus chemist sales',
        'created_by_user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($id1)->not->toBeNull()
        ->and($id2)->not->toBeNull()
        ->and(DB::table('doctor_revenues')->where('doctor_id', $doctor->id)->count())->toBe(2);

    $totalRevenue = DB::table('doctor_revenues')
        ->where('doctor_id', $doctor->id)
        ->where('period_date', '2026-10-01')
        ->sum('amount');

    expect((float) $totalRevenue)->toBe(7700.00);
});

it('cascades doctor deletion to doctor_revenues', function () {
    $doctor = Doctor::factory()->create();

    DB::table('doctor_revenues')->insert([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-10-01',
        'amount' => 5000.00,
        'source' => 'manual_entry',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('doctor_revenues')->where('doctor_id', $doctor->id)->count())->toBe(1);

    $doctor->delete();

    expect(DB::table('doctor_revenues')->where('doctor_id', $doctor->id)->count())->toBe(0);
});
