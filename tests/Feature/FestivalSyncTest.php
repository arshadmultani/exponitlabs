<?php

use App\Models\Festival;
use App\Models\FestivalDate;
use App\Services\FestivalGreetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('returns cached multi-year festivals payload on master-data sync', function () {
    Artisan::call('festivals:import');

    $response = $this->getJson('/api/v1/sync/master-data');

    $response->assertOk()
        ->assertJsonStructure([
            'server_time',
            'doctors',
            'products',
            'promotional_inputs',
            'visit_history',
            'festivals' => [
                '*' => [
                    'id',
                    'festival_id',
                    'name',
                    'slug',
                    'category',
                    'target_specialties',
                    'date',
                    'window_start',
                    'window_end',
                    'greeting_message',
                ],
            ],
        ]);

    $data = $response->json('festivals');
    expect($data)->not->toBeEmpty();

    // Verify server-side caching
    expect(Cache::has('active_festivals_master_data'))->toBeTrue();
});

it('preserves festivals payload regardless of since timestamp parameter', function () {
    Artisan::call('festivals:import');

    $sinceTime = Carbon::now()->addYear()->toIso8601String();

    $response = $this->getJson("/api/v1/sync/master-data?since={$sinceTime}");

    $response->assertOk();
    $festivals = $response->json('festivals');

    expect($festivals)->not->toBeEmpty();
});

it('spans current year minus 1 to current year plus 1 in the synced festivals payload', function () {
    Artisan::call('festivals:import');

    $currentYear = Carbon::now(FestivalGreetingService::TIMEZONE)->year;

    $response = $this->getJson('/api/v1/sync/master-data');
    $festivals = collect($response->json('festivals'));

    $years = $festivals->map(fn ($item) => (int) substr($item['date'], 0, 4))->unique()->sort()->values()->all();

    expect($years)->toContain($currentYear - 1)
        ->toContain($currentYear)
        ->toContain($currentYear + 1);
});

it('invalidates cached festivals on date adjustment so subsequent sync fetches updated date', function () {
    Artisan::call('festivals:import');

    // First call caches the payload
    $this->getJson('/api/v1/sync/master-data')->assertOk();
    expect(Cache::has('active_festivals_master_data'))->toBeTrue();

    $diwali = Festival::where('slug', 'diwali')->firstOrFail();
    $currentYear = Carbon::now(FestivalGreetingService::TIMEZONE)->year;
    $diwaliDate = FestivalDate::where('festival_id', $diwali->id)->where('year', $currentYear)->firstOrFail();

    // Shift date
    app(FestivalGreetingService::class)->adjustFestivalDate(
        $diwaliDate,
        "{$currentYear}-11-15",
        'Test Shift',
        null
    );

    // Cache must have been busted
    expect(Cache::has('active_festivals_master_data'))->toBeFalse();

    // Next sync should return the shifted date
    $response = $this->getJson('/api/v1/sync/master-data');
    $festivals = collect($response->json('festivals'));
    $shifted = $festivals->first(fn ($item) => $item['slug'] === 'diwali' && str_starts_with($item['date'], (string) $currentYear));

    expect($shifted['date'])->toBe("{$currentYear}-11-15");
});
