<?php

use App\Models\DCR;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\Product;
use App\Models\PromotionalInput;
use App\Services\DoctorPortfolioQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('calculates portfolio metrics with correlated subqueries and enables native database sorting', function () {
    config(['exponit.default_visit_cost' => 250.00]);

    // Doctor A: 2 visits, 10 samples (unit cost 20), 2 promo inputs (unit cost 50), revenue 3000
    // Total investment = (2 * 250) + (10 * 20) + (2 * 50) = 500 + 200 + 100 = 800
    // Net PnL = 3000 - 800 = 2200
    $doctorA = Doctor::factory()->create(['name' => 'Dr. Alpha']);
    $productA = Product::factory()->create(['unit_cost' => 20.00, 'sample_cost' => null]);
    $promoA = PromotionalInput::create(['name' => 'Pen', 'unit_cost' => 50.00]);

    $dcrA1 = DCR::factory()->create(['doctor_id' => $doctorA->id, 'date' => '2026-10-05']);
    $dcrA2 = DCR::factory()->create(['doctor_id' => $doctorA->id, 'date' => '2026-10-15']);

    DB::table('dcr_products')->insert([
        ['dcr_id' => $dcrA1->id, 'product_id' => $productA->id, 'quantity' => 6, 'created_at' => now(), 'updated_at' => now()],
        ['dcr_id' => $dcrA2->id, 'product_id' => $productA->id, 'quantity' => 4, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('dcr_promotional_inputs')->insert([
        ['dcr_id' => $dcrA1->id, 'promotional_input_id' => $promoA->id, 'quantity' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DoctorRevenue::factory()->create([
        'doctor_id' => $doctorA->id,
        'period_date' => '2026-10-01',
        'amount' => 3000.00,
    ]);

    // Doctor B: 4 visits, no revenue -> Net PnL = -1000
    $doctorB = Doctor::factory()->create(['name' => 'Dr. Beta']);
    for ($i = 1; $i <= 4; $i++) {
        DCR::factory()->create(['doctor_id' => $doctorB->id, 'date' => '2026-10-1'.$i]);
    }

    $service = new DoctorPortfolioQueryService;
    $startDate = Carbon::parse('2026-10-01');
    $endDate = Carbon::parse('2026-10-31');

    $query = $service->getBaseQuery($startDate, $endDate);

    // Test sorting by net_pnl DESC natively in SQLite
    $sortedDesc = (clone $query)->orderBy('net_pnl', 'desc')->get();
    expect($sortedDesc->first()->id)->toBe($doctorA->id)
        ->and((float) $sortedDesc->first()->net_pnl)->toBe(2200.00)
        ->and((float) $sortedDesc->first()->total_investment)->toBe(800.00)
        ->and((float) $sortedDesc->first()->total_revenue)->toBe(3000.00)
        ->and((int) $sortedDesc->first()->visits_count)->toBe(2)
        ->and((float) $sortedDesc->first()->samples_cost)->toBe(200.00)
        ->and((float) $sortedDesc->first()->promos_cost)->toBe(100.00)
        ->and($sortedDesc->last()->id)->toBe($doctorB->id)
        ->and((float) $sortedDesc->last()->net_pnl)->toBe(-1000.00);

    // Test sorting by net_pnl ASC natively in SQLite
    $sortedAsc = (clone $query)->orderBy('net_pnl', 'asc')->get();
    expect($sortedAsc->first()->id)->toBe($doctorB->id)
        ->and($sortedAsc->last()->id)->toBe($doctorA->id);
});

it('respects doctor visit_cost_override over global default cost', function () {
    config(['exponit.default_visit_cost' => 250.00]);

    // Doctor with custom override of 400.00 per visit
    $doctor = Doctor::factory()->create([
        'visit_cost_override' => 400.00,
    ]);

    DCR::factory()->count(3)->create([
        'doctor_id' => $doctor->id,
        'date' => '2026-10-10',
    ]);

    DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-10-01',
        'amount' => 2000.00,
    ]);

    $service = new DoctorPortfolioQueryService;
    $result = $service->getBaseQuery()->where('doctors.id', $doctor->id)->first();

    // 3 visits * 400 = 1200 investment; Net PnL = 2000 - 1200 = 800
    expect((float) $result->total_investment)->toBe(1200.00)
        ->and((float) $result->net_pnl)->toBe(800.00);
});

it('prioritizes unit_cost_snapshot over live master catalog prices', function () {
    $doctor = Doctor::factory()->create();
    $product = Product::factory()->create(['unit_cost' => 100.00]);

    $dcr = DCR::factory()->create(['doctor_id' => $doctor->id, 'date' => '2026-10-05']);

    // Logged with snapshot cost of 50.00 (before catalog price increased to 100.00)
    DB::table('dcr_products')->insert([
        'dcr_id' => $dcr->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_cost_snapshot' => 50.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new DoctorPortfolioQueryService;
    $result = $service->getBaseQuery()->where('doctors.id', $doctor->id)->first();

    // 2 * 50 = 100.00 sample cost (not 2 * 100 = 200.00)
    expect((float) $result->samples_cost)->toBe(100.00);
});

it('snaps revenue start date to beginning of month to prevent rolling date revenue exclusion', function () {
    $doctor = Doctor::factory()->create();

    // DCR logged on Aug 15
    DCR::factory()->create(['doctor_id' => $doctor->id, 'date' => '2026-08-15']);

    // Revenue normalized to Aug 1
    DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-08-01',
        'amount' => 5000.00,
    ]);

    // Query with rolling intra-month date (e.g. Aug 10 to Nov 1)
    $startDate = Carbon::parse('2026-08-10');
    $endDate = Carbon::parse('2026-11-01');

    $service = new DoctorPortfolioQueryService;
    $result = $service->getBaseQuery($startDate, $endDate)->where('doctors.id', $doctor->id)->first();

    // Revenue of 5000 should NOT be excluded because start date snaps to 2026-08-01 for revenue
    expect((float) $result->total_revenue)->toBe(5000.00);
});

it('resolves financial year and calendar horizon ranges accurately', function () {
    $service = new DoctorPortfolioQueryService;

    // Test Indian FY (April 1 to March 31)
    [$fyStart, $fyEnd] = $service->resolveHorizonRange('current_fy');
    expect($fyStart)->not->toBeNull()
        ->and($fyEnd)->not->toBeNull()
        ->and($fyStart->month)->toBe(4)
        ->and($fyStart->day)->toBe(1)
        ->and($fyEnd->month)->toBe(3)
        ->and($fyEnd->day)->toBe(31);

    // Test Last 3 Months
    [$threeMStart, $threeMEnd] = $service->resolveHorizonRange('last_3_months');
    expect($threeMStart)->not->toBeNull()
        ->and($threeMStart->day)->toBe(1);

    // Test All Time
    [$allStart, $allEnd] = $service->resolveHorizonRange('all_time');
    expect($allStart)->toBeNull()
        ->and($allEnd)->toBeNull();
});

it('calculates portfolio aggregated stats and return multiple with zero-division safeguard', function () {
    config(['exponit.default_visit_cost' => 200.00]);

    $docA = Doctor::factory()->create();
    DCR::factory()->count(2)->create(['doctor_id' => $docA->id]); // 400 investment
    DoctorRevenue::factory()->create(['doctor_id' => $docA->id, 'amount' => 1200.00]); // 1200 revenue

    $service = new DoctorPortfolioQueryService;
    $stats = $service->calculatePortfolioStats();

    expect($stats['total_investment'])->toBe(400.00)
        ->and($stats['total_revenue'])->toBe(1200.00)
        ->and($stats['net_pnl'])->toBe(800.00)
        ->and($stats['return_multiple'])->toBe(3.0);

    // Test zero-investment safeguard
    expect($service->calculateReturnMultiple(0.0, 1000.0))->toBe(0.0)
        ->and($service->calculateRoiPercentage(0.0, 1000.0))->toBeNull();
});

it('resolves deterministic performance tiers in strict precedence order', function () {
    $service = new DoctorPortfolioQueryService;

    // 1. Incubation: created within 60 days
    $incubationDoctor = (object) [
        'created_at' => now()->subDays(10)->toIso8601String(),
        'net_pnl' => -500.00,
        'total_investment' => 500.00,
    ];
    expect($service->resolveTier($incubationDoctor))->toBe('incubation');

    // 2. Under-yielding: Net PnL < 0
    $underYieldingDoctor = (object) [
        'created_at' => now()->subDays(90)->toIso8601String(),
        'net_pnl' => -200.00,
        'total_investment' => 800.00,
    ];
    expect($service->resolveTier($underYieldingDoctor))->toBe('under_yielding');

    // 3. High-Yield: Net PnL > 0 and ROI >= 50%
    $highYieldDoctor = (object) [
        'created_at' => now()->subDays(120)->toIso8601String(),
        'net_pnl' => 600.00,
        'total_investment' => 1000.00, // ROI = 60%
    ];
    expect($service->resolveTier($highYieldDoctor))->toBe('high_yield');

    // 4. Balanced: Net PnL >= 0 and ROI < 50%
    $balancedDoctor = (object) [
        'created_at' => now()->subDays(120)->toIso8601String(),
        'net_pnl' => 300.00,
        'total_investment' => 1000.00, // ROI = 30%
    ];
    expect($service->resolveTier($balancedDoctor))->toBe('balanced');
});
