<?php

use App\Models\Festival;
use App\Models\FestivalDate;
use App\Models\User;
use App\Services\FestivalGreetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(FestivalGreetingService::class);
});

it('calculates active greeting window boundaries deterministically', function () {
    $festival = Festival::factory()->create([
        'name' => 'Diwali',
        'category' => 'cultural_and_national',
        'days_before' => 3,
        'days_after' => 1,
        'is_active' => true,
    ]);

    FestivalDate::factory()->create([
        'festival_id' => $festival->id,
        'year' => 2025,
        'date' => '2025-11-01',
    ]);

    // Oct 28: 4 days before -> Inactive
    expect($this->service->getActiveObservancesForDate(Carbon::parse('2025-10-28 12:00:00')))->toBeEmpty();

    // Oct 29: 3 days before -> Active
    $activeStart = $this->service->getActiveObservancesForDate(Carbon::parse('2025-10-29 09:00:00'));
    expect($activeStart)->toHaveCount(1)
        ->and($activeStart->first()->festival->name)->toBe('Diwali');

    // Nov 01: Observance day -> Active
    $activeDay = $this->service->getActiveObservancesForDate(Carbon::parse('2025-11-01 18:30:00'));
    expect($activeDay)->toHaveCount(1);

    // Nov 02: 1 day after -> Active
    $activeEnd = $this->service->getActiveObservancesForDate(Carbon::parse('2025-11-02 23:59:00'));
    expect($activeEnd)->toHaveCount(1);

    // Nov 03: 2 days after -> Inactive
    expect($this->service->getActiveObservancesForDate(Carbon::parse('2025-11-03 00:01:00')))->toBeEmpty();
});

it('treats cultural and national observances as universal to all doctors', function () {
    $festival = Festival::factory()->create([
        'category' => 'cultural_and_national',
        'days_before' => 2,
        'days_after' => 1,
        'is_active' => true,
    ]);

    FestivalDate::factory()->create([
        'festival_id' => $festival->id,
        'year' => 2026,
        'date' => '2026-08-15',
    ]);

    $evalDate = Carbon::parse('2026-08-15');

    // Null specialty (universal)
    expect($this->service->getActiveObservancesForDate($evalDate, null))->toHaveCount(1);

    // Any specialty (universal)
    expect($this->service->getActiveObservancesForDate($evalDate, 'Cardiology'))->toHaveCount(1);
    expect($this->service->getActiveObservancesForDate($evalDate, 'Ophthalmology'))->toHaveCount(1);
});

it('filters clinical observances by physician specialty with fuzzy and root stemming matching', function () {
    $heartDay = Festival::factory()->clinical(['Cardiology', 'Internal Medicine'])->create([
        'name' => 'World Heart Day',
        'days_before' => 2,
        'days_after' => 1,
    ]);

    FestivalDate::factory()->create([
        'festival_id' => $heartDay->id,
        'year' => 2025,
        'date' => '2025-09-29',
    ]);

    $evalDate = Carbon::parse('2025-09-29');

    // Exact match
    expect($this->service->getActiveObservancesForDate($evalDate, 'Cardiology'))->toHaveCount(1);

    // Variation "Cardiologist" matches root "cardio"
    expect($this->service->getActiveObservancesForDate($evalDate, 'Cardiologist'))->toHaveCount(1);

    // Substring match
    expect($this->service->getActiveObservancesForDate($evalDate, 'Consultant Interventional Cardiologist'))->toHaveCount(1);

    // Unrelated specialty -> Inactive
    expect($this->service->getActiveObservancesForDate($evalDate, 'Orthopaedics'))->toBeEmpty();

    // Doctor with null/empty specialty -> Inactive for clinical observance
    expect($this->service->getActiveObservancesForDate($evalDate, null))->toBeEmpty();
    expect($this->service->getActiveObservancesForDate($evalDate, ''))->toBeEmpty();
});

it('normalizes paediatric spelling variations between British and American English', function () {
    $nutritionWeek = Festival::factory()->clinical(['Paediatrics', 'Endocrinology'])->create([
        'name' => 'National Nutrition Week',
        'days_before' => 1,
        'days_after' => 1,
    ]);

    FestivalDate::factory()->create([
        'festival_id' => $nutritionWeek->id,
        'year' => 2025,
        'date' => '2025-09-01',
    ]);

    $evalDate = Carbon::parse('2025-09-01');

    // British English
    expect($this->service->getActiveObservancesForDate($evalDate, 'Paediatrician'))->toHaveCount(1);

    // American English
    expect($this->service->getActiveObservancesForDate($evalDate, 'Pediatrician'))->toHaveCount(1);
});

it('generates a 42-cell Monday-anchored month grid with correct metadata', function () {
    Artisan::call('festivals:import');

    // October 2025: Oct 1 is Wednesday, so Monday-anchored grid starts on Monday Sep 29, 2025
    $grid = $this->service->getMonthGridData(2025, 10);

    expect($grid)->toHaveCount(42);

    // First cell should be 2025-09-29 (from previous month)
    expect($grid[0]['date'])->toBe('2025-09-29')
        ->and($grid[0]['day'])->toBe(29)
        ->and($grid[0]['is_current_month'])->toBeFalse();

    // 2025-10-01 should be current month
    $oct1 = collect($grid)->firstWhere('date', '2025-10-01');
    expect($oct1)->not->toBeNull()
        ->and($oct1['is_current_month'])->toBeTrue();

    // 2025-10-20 is Diwali
    $diwaliCell = collect($grid)->firstWhere('date', '2025-10-20');
    expect($diwaliCell['festivals'])->not->toBeEmpty()
        ->and($diwaliCell['festivals']->first()->festival->slug)->toBe('diwali');

    // Test category filtering on the grid
    $culturalGrid = $this->service->getMonthGridData(2025, 10, 'cultural_and_national');
    $clinicalGrid = $this->service->getMonthGridData(2025, 10, 'clinical_observance');

    $culturalDiwali = collect($culturalGrid)->firstWhere('date', '2025-10-20');
    $clinicalDiwali = collect($clinicalGrid)->firstWhere('date', '2025-10-20');

    expect($culturalDiwali['festivals'])->not->toBeEmpty()
        ->and($clinicalDiwali['festivals'])->toBeEmpty();
});

it('manages manual date adjustment and restores baseline from JSON', function () {
    Artisan::call('festivals:import');

    $eid = Festival::where('slug', 'eid-ul-fitr')->firstOrFail();
    $eidDate = FestivalDate::where('festival_id', $eid->id)->where('year', 2025)->firstOrFail();
    $user = User::factory()->create();

    // Canonical baseline should be 2025-03-31
    expect($this->service->getFestivalDateBaseline($eidDate))->toBe('2025-03-31');

    // Adjust date
    Cache::put('active_festivals_master_data', 'cached_data', 3600);
    $adjusted = $this->service->adjustFestivalDate($eidDate, '2025-04-01', 'Hilal Committee update', $user->id);

    expect($adjusted)->toBeTrue()
        ->and(Cache::has('active_festivals_master_data'))->toBeFalse();

    $eidDate->refresh();
    expect($eidDate->date->format('Y-m-d'))->toBe('2025-04-01')
        ->and($eidDate->is_manually_adjusted)->toBeTrue()
        ->and($eidDate->adjustment_notes)->toBe('Hilal Committee update')
        ->and($eidDate->adjusted_by_user_id)->toBe($user->id);

    // Reset back to baseline
    Cache::put('active_festivals_master_data', 'cached_data', 3600);
    $reset = $this->service->resetFestivalDateToBaseline($eidDate);

    expect($reset)->toBeTrue()
        ->and(Cache::has('active_festivals_master_data'))->toBeFalse();

    $eidDate->refresh();
    expect($eidDate->date->format('Y-m-d'))->toBe('2025-03-31')
        ->and($eidDate->is_manually_adjusted)->toBeFalse()
        ->and($eidDate->adjustment_notes)->toBeNull()
        ->and($eidDate->adjusted_by_user_id)->toBeNull();
});
