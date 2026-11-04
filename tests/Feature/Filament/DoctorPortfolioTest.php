<?php

use App\Filament\Pages\DoctorPortfolio;
use App\Models\AppSetting;
use App\Models\Area;
use App\Models\DCR;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Enums\RecordActionsPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the doctor portfolio page for authenticated users', function () {
    $this->get('/console/doctor-portfolio')
        ->assertSuccessful()
        ->assertSee('Doctor Commercial Performance & ROI');
});

it('displays doctors in the portfolio table with calculated metrics', function () {
    $area = Area::create(['name' => 'South Mumbai']);
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. A. K. Sharma',
        'area_id' => $area->id,
        'visit_cost_override' => 300.00,
        'created_at' => now()->subMonths(6),
    ]);

    // Create 2 visits in current FY
    DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => now()->startOfMonth()->toDateString(),
    ]);
    DCR::factory()->create([
        'doctor_id' => $doctor->id,
        'date' => now()->startOfMonth()->toDateString(),
    ]);

    // Detailing investment = 2 * 300 = 600
    // Log revenue of 2,000 => Net P&L = +1,400, ROI = 233.33% (High-Yield)
    DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => now()->startOfMonth()->toDateString(),
        'amount' => 2000.00,
        'created_by_user_id' => $this->user->id,
    ]);

    Livewire::test(DoctorPortfolio::class)
        ->assertCanSeeTableRecords([$doctor])
        ->assertTableColumnStateSet('name', 'Dr. A. K. Sharma', $doctor)
        ->assertTableColumnStateSet('visits_count', 2, $doctor)
        ->assertTableColumnStateSet('total_investment', 600.00, $doctor)
        ->assertTableColumnStateSet('total_revenue', 2000.00, $doctor)
        ->assertTableColumnStateSet('net_pnl', 1400.00, $doctor)
        ->assertTableColumnStateSet('tier', 'high_yield', $doctor);
});

it('sorts prescribers by net_pnl descending by default', function () {
    $docProfitable = Doctor::factory()->create([
        'name' => 'Dr. High Yield',
        'created_at' => now()->subMonths(6),
    ]);
    $docDeficit = Doctor::factory()->create([
        'name' => 'Dr. Low Return',
        'created_at' => now()->subMonths(6),
    ]);

    // Dr. High Yield has 5,000 revenue
    DoctorRevenue::factory()->create([
        'doctor_id' => $docProfitable->id,
        'period_date' => now()->startOfMonth()->toDateString(),
        'amount' => 5000.00,
    ]);

    // Dr. Low Return has 10 visits @ 250 = 2,500 cost, 0 revenue => Net P&L = -2,500
    for ($i = 0; $i < 10; $i++) {
        DCR::factory()->create([
            'doctor_id' => $docDeficit->id,
            'date' => now()->startOfMonth()->toDateString(),
        ]);
    }

    Livewire::test(DoctorPortfolio::class)
        ->assertCanSeeTableRecords([$docProfitable, $docDeficit], inOrder: true);
});

it('allows switching time-horizon tabs', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Horizon Test']);

    // Revenue in current month
    DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => now()->startOfMonth()->toDateString(),
        'amount' => 4500.00,
    ]);

    // Revenue 18 months ago (outside current FY)
    DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => now()->subMonths(18)->startOfMonth()->toDateString(),
        'amount' => 10000.00,
    ]);

    $component = Livewire::test(DoctorPortfolio::class);

    // In current FY tab: revenue is 4,500
    $component->assertTableColumnStateSet('total_revenue', 4500.00, $doctor);

    // Switch to all_time tab: revenue should be 14,500
    $component->set('activeTab', 'all_time')
        ->assertTableColumnStateSet('total_revenue', 14500.00, $doctor);
});

it('logs attributed revenue via header action', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Header Revenue']);

    $testDate = '2026-08-15';

    Livewire::test(DoctorPortfolio::class)
        ->callAction('logRevenue', [
            'doctor_id' => $doctor->id,
            'period_date' => $testDate,
            'amount' => 15500.00,
            'notes' => 'August Primary Rx Audit',
        ])
        ->assertHasNoActionErrors();

    $revenue = DoctorRevenue::where('doctor_id', $doctor->id)->first();
    expect($revenue)->not->toBeNull()
        ->and($revenue->amount)->toBe('15500.00')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-08-01')
        ->and($revenue->notes)->toBe('August Primary Rx Audit')
        ->and($revenue->created_by_user_id)->toBe($this->user->id);
});

it('logs attributed revenue via record action on table', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Row Revenue']);

    Livewire::test(DoctorPortfolio::class)
        ->callAction(TestAction::make('logDoctorRevenue')->table($doctor), [
            'period_date' => '2026-09-10',
            'amount' => 8200.00,
            'notes' => 'Direct clinic sample audit',
        ])
        ->assertHasNoActionErrors();

    $revenue = DoctorRevenue::where('doctor_id', $doctor->id)->first();
    expect($revenue)->not->toBeNull()
        ->and($revenue->amount)->toBe('8200.00')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-09-01')
        ->and($revenue->notes)->toBe('Direct clinic sample audit')
        ->and($revenue->created_by_user_id)->toBe($this->user->id);
});

it('imports revenue records from CSV via header action', function () {
    Storage::fake('local');
    $doctor = Doctor::factory()->create();

    $csvContent = "doctor_id,period_date,amount,notes\n{$doctor->id},2026-10-01,25000.00,October Wholesaler Audit\n";
    $file = UploadedFile::fake()->createWithContent('revenue.csv', $csvContent);

    Livewire::test(DoctorPortfolio::class)
        ->callAction('importCsv', [
            'csv_file' => [$file],
        ])
        ->assertHasNoActionErrors();

    $revenue = DoctorRevenue::where('doctor_id', $doctor->id)->first();
    expect($revenue)->not->toBeNull()
        ->and($revenue->amount)->toBe('25000.00')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-10-01')
        ->and($revenue->notes)->toBe('October Wholesaler Audit')
        ->and($revenue->created_by_user_id)->toBe($this->user->id);
});

it('generates a pre-filled revenue template CSV for download', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Template Test']);

    $response = Livewire::test(DoctorPortfolio::class)
        ->callAction('downloadTemplate', [
            'period_date' => '2026-11-15',
        ]);

    $response->assertFileDownloaded('doctor_revenue_template_2026-11-01.csv');
});

it('supports create and create another on record log revenue action', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Sequential Entry']);

    Livewire::test(DoctorPortfolio::class)
        ->callAction(TestAction::make('logDoctorRevenue')->table($doctor), [
            'period_date' => '2026-11-05',
            'amount' => 5000.00,
            'notes' => 'Batch 1',
        ], ['another' => true])
        ->assertActionHalted(TestAction::make('logDoctorRevenue')->table($doctor))
        ->assertNotified("Revenue logged for {$doctor->name}");

    expect(DoctorRevenue::where('doctor_id', $doctor->id)->count())->toBe(1);

    $revenue = DoctorRevenue::where('doctor_id', $doctor->id)->first();
    expect($revenue->amount)->toBe('5000.00')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-11-01')
        ->and($revenue->notes)->toBe('Batch 1');
});

it('supports create and create another on header log revenue action', function () {
    $doctor1 = Doctor::factory()->create(['name' => 'Dr. Alpha']);

    Livewire::test(DoctorPortfolio::class)
        ->callAction('logRevenue', [
            'doctor_id' => $doctor1->id,
            'period_date' => '2026-11-10',
            'amount' => 12000.00,
            'notes' => 'November Alpha Stockist',
        ], ['another' => true])
        ->assertActionHalted('logRevenue')
        ->assertNotified('Attributed revenue logged successfully');

    expect(DoctorRevenue::where('doctor_id', $doctor1->id)->count())->toBe(1);

    $revenue = DoctorRevenue::where('doctor_id', $doctor1->id)->first();
    expect($revenue->amount)->toBe('12000.00')
        ->and($revenue->period_date->format('Y-m-d'))->toBe('2026-11-01')
        ->and($revenue->notes)->toBe('November Alpha Stockist');
});

it('supports logging multiple revenue entries consecutively using create another', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Multi Entry']);

    Livewire::test(DoctorPortfolio::class)
        ->callAction(TestAction::make('logDoctorRevenue')->table($doctor), [
            'period_date' => '2026-10-01',
            'amount' => 3000.00,
            'notes' => 'Month 1',
        ], ['another' => true])
        ->assertActionHalted(TestAction::make('logDoctorRevenue')->table($doctor))
        ->fillForm([
            'amount' => 4500.00,
            'notes' => 'Month 2',
        ])
        ->callMountedAction();

    expect(DoctorRevenue::where('doctor_id', $doctor->id)->count())->toBe(2);
});

it('allows viewing and editing logged revenues for a doctor via manageDoctorRevenues action', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Edit Revenue Test']);

    $rev1 = DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-08-01',
        'amount' => 5000.00,
        'notes' => 'Old August note',
    ]);

    $rev2 = DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-09-01',
        'amount' => 8000.00,
        'notes' => 'Old September note',
    ]);

    Livewire::test(DoctorPortfolio::class)
        ->callAction(TestAction::make('manageDoctorRevenues')->table($doctor), [
            'revenues' => [
                "record-{$rev1->id}" => [
                    'id' => $rev1->id,
                    'period_date' => '2026-08-01',
                    'amount' => 6500.00, // updated amount
                    'notes' => 'Updated August note',
                ],
                "record-{$rev2->id}" => [
                    'id' => $rev2->id,
                    'period_date' => '2026-09-01',
                    'amount' => 8000.00,
                    'notes' => 'Old September note',
                ],
            ],
        ])
        ->assertHasNoActionErrors()
        ->assertNotified("Attributed revenue updated for {$doctor->name}");

    $rev1->refresh();
    expect($rev1->amount)->toBe('6500.00')
        ->and($rev1->notes)->toBe('Updated August note');
});

it('supports adding and deleting revenues in manageDoctorRevenues action', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. Add Delete Revenue']);

    $revToKeep = DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-07-01',
        'amount' => 4000.00,
    ]);

    $revToDelete = DoctorRevenue::factory()->create([
        'doctor_id' => $doctor->id,
        'period_date' => '2026-08-01',
        'amount' => 3000.00,
    ]);

    // Omit $revToDelete by mounting the action and setting data with only the kept record and a new record
    Livewire::test(DoctorPortfolio::class)
        ->mountAction(TestAction::make('manageDoctorRevenues')->table($doctor))
        ->set('mountedActions.0.data.revenues', [
            "record-{$revToKeep->id}" => [
                'id' => $revToKeep->id,
                'period_date' => '2026-07-01',
                'amount' => 4200.00,
                'notes' => 'Kept and adjusted',
            ],
            'new-1' => [
                'id' => null,
                'period_date' => '2026-10-15',
                'amount' => 9500.00,
                'notes' => 'Brand new October entry',
            ],
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    // Deleted record should be gone
    expect(DoctorRevenue::where('id', $revToDelete->id)->exists())->toBeFalse();

    // Updated record should have new values
    $revToKeep->refresh();
    expect($revToKeep->amount)->toBe('4200.00');

    // New record should exist
    $newEntry = DoctorRevenue::where('doctor_id', $doctor->id)
        ->whereDate('period_date', '2026-10-01')
        ->first();
    expect($newEntry)->not->toBeNull()
        ->and($newEntry->amount)->toBe('9500.00')
        ->and($newEntry->notes)->toBe('Brand new October entry');
});

it('displays doctor area as description and allows searching doctors by area name', function () {
    $areaWest = Area::create(['name' => 'Vasai West']);
    $areaEast = Area::create(['name' => 'Nalasopara East']);

    $doctorWest = Doctor::factory()->create([
        'name' => 'Dr. West Prescriber',
        'area_id' => $areaWest->id,
    ]);

    $doctorEast = Doctor::factory()->create([
        'name' => 'Dr. East Prescriber',
        'area_id' => $areaEast->id,
    ]);

    Livewire::test(DoctorPortfolio::class)
        ->assertCanSeeTableRecords([$doctorWest, $doctorEast])
        ->searchTable('Vasai West')
        ->assertCanSeeTableRecords([$doctorWest])
        ->assertCanNotSeeTableRecords([$doctorEast]);
});

it('allows adjusting the global visit cost in console and recalculates portfolio investments', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Baseline Cost Test',
        'visit_cost_override' => null, // rely on global baseline
    ]);

    // Create 4 visits in current FY
    for ($i = 0; $i < 4; $i++) {
        DCR::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => now()->startOfMonth()->toDateString(),
        ]);
    }

    // Baseline is 250: 4 * 250 = 1000
    Livewire::test(DoctorPortfolio::class)
        ->assertTableColumnStateSet('total_investment', 1000.00, $doctor);

    // Adjust global visit cost to 450
    Livewire::test(DoctorPortfolio::class)
        ->callAction('adjustGlobalVisitCost', [
            'default_visit_cost' => 450.00,
        ])
        ->assertHasNoActionErrors()
        ->assertNotified('Global visit cost updated to ₹450.00');

    expect(AppSetting::getFloat('default_visit_cost'))->toBe(450.00);

    // Now 4 * 450 = 1800
    Livewire::test(DoctorPortfolio::class)
        ->assertTableColumnStateSet('total_investment', 1800.00, $doctor)
        ->assertTableColumnStateSet('net_pnl', -1800.00, $doctor);
});

it('positions the log revenue action as the first column with Log label', function () {
    $doctor = Doctor::factory()->create(['name' => 'Dr. First Col Log']);

    $test = Livewire::test(DoctorPortfolio::class);
    $table = $test->instance()->getTable();

    expect($table->getRecordActionsPosition())->toBe(RecordActionsPosition::BeforeColumns);

    $action = $table->getAction('logDoctorRevenue');
    expect($action)->not->toBeNull()
        ->and($action->getLabel())->toBe('Log');
});
