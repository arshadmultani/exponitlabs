<?php

use App\Filament\Pages\DoctorPortfolio;
use App\Models\Area;
use App\Models\DCR;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
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
