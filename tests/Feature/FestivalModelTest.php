<?php

use App\Models\Festival;
use App\Models\FestivalDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('verifies festival model casts and factory', function () {
    $festival = Festival::factory()->create([
        'name' => 'Diwali',
        'slug' => 'diwali',
        'category' => 'cultural_and_national',
        'target_specialties' => null,
        'days_before' => 4,
        'days_after' => 2,
        'is_active' => true,
    ]);

    $festival->refresh();
    expect($festival->name)->toBe('Diwali')
        ->and($festival->slug)->toBe('diwali')
        ->and($festival->category)->toBe('cultural_and_national')
        ->and($festival->target_specialties)->toBeNull()
        ->and($festival->days_before)->toBe(4)
        ->and($festival->days_after)->toBe(2)
        ->and($festival->is_active)->toBeTrue();

    $clinical = Festival::factory()->clinical(['Cardiology', 'Nephrology'])->create();
    $clinical->refresh();
    expect($clinical->category)->toBe('clinical_observance')
        ->and($clinical->target_specialties)->toBe(['Cardiology', 'Nephrology']);
});

it('verifies festival date model casts and relationships', function () {
    $user = User::factory()->create();
    $festival = Festival::factory()->create();

    $festivalDate = FestivalDate::factory()->manuallyAdjusted('Moon sighting verified')->create([
        'festival_id' => $festival->id,
        'year' => 2026,
        'date' => '2026-03-21',
        'adjusted_by_user_id' => $user->id,
    ]);

    $festivalDate->refresh();
    expect($festivalDate->year)->toBe(2026)
        ->and($festivalDate->date)->toBeInstanceOf(Carbon::class)
        ->and($festivalDate->date->format('Y-m-d'))->toBe('2026-03-21')
        ->and($festivalDate->is_manually_adjusted)->toBeTrue()
        ->and($festivalDate->adjustment_notes)->toBe('Moon sighting verified')
        ->and($festivalDate->festival->id)->toBe($festival->id)
        ->and($festivalDate->adjustedBy->id)->toBe($user->id);

    expect($festival->dates)->toHaveCount(1)
        ->and($festival->dates->first()->id)->toBe($festivalDate->id);
});

it('cascades delete from festival to festival dates', function () {
    $festival = Festival::factory()->create();
    FestivalDate::factory()->count(3)->create(['festival_id' => $festival->id]);

    expect(FestivalDate::where('festival_id', $festival->id)->count())->toBe(3);

    $festival->delete();

    expect(FestivalDate::where('festival_id', $festival->id)->count())->toBe(0);
});

it('runs festivals:import command and seeds 2025-2030 dataset', function () {
    Cache::put('active_festivals_master_data', 'stale_cache_test', 3600);

    $exitCode = Artisan::call('festivals:import');

    expect($exitCode)->toBe(0)
        ->and(Cache::has('active_festivals_master_data'))->toBeFalse()
        ->and(Festival::count())->toBe(29)
        ->and(FestivalDate::count())->toBe(174);

    $diwali = Festival::where('slug', 'diwali')->with('dates')->firstOrFail();
    expect($diwali->category)->toBe('cultural_and_national')
        ->and($diwali->dates)->toHaveCount(6);

    $diwali2025 = $diwali->dates->firstWhere('year', 2025);
    expect($diwali2025->date->format('Y-m-d'))->toBe('2025-10-20')
        ->and($diwali2025->is_manually_adjusted)->toBeFalse();
});

it('preserves manual overrides during regular import and resets them with --force-reset-overrides', function () {
    Artisan::call('festivals:import');

    $eid = Festival::where('slug', 'eid-ul-fitr')->firstOrFail();
    $eid2025 = FestivalDate::where('festival_id', $eid->id)->where('year', 2025)->firstOrFail();

    // Manually shift date by 1 day
    $user = User::factory()->create();
    $eid2025->update([
        'date' => '2025-04-01',
        'is_manually_adjusted' => true,
        'adjusted_by_user_id' => $user->id,
        'adjustment_notes' => 'Moon sighting delayed by 1 day',
    ]);

    // Regular import must preserve the manual override
    Artisan::call('festivals:import');
    $eid2025->refresh();

    expect($eid2025->date->format('Y-m-d'))->toBe('2025-04-01')
        ->and($eid2025->is_manually_adjusted)->toBeTrue()
        ->and($eid2025->adjustment_notes)->toBe('Moon sighting delayed by 1 day');

    // Force reset must restore the canonical baseline from JSON
    Artisan::call('festivals:import', ['--force-reset-overrides' => true]);
    $eid2025->refresh();

    expect($eid2025->date->format('Y-m-d'))->toBe('2025-03-31')
        ->and($eid2025->is_manually_adjusted)->toBeFalse()
        ->and($eid2025->adjustment_notes)->toBeNull()
        ->and($eid2025->adjusted_by_user_id)->toBeNull();
});

it('runs festival seeder successfully', function () {
    $exitCode = Artisan::call('db:seed', ['--class' => 'FestivalSeeder']);

    expect($exitCode)->toBe(0)
        ->and(Festival::count())->toBeGreaterThan(0);
});
