<?php

use App\Models\Festival;
use App\Models\FestivalDate;
use App\Models\User;
use App\Services\FestivalImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(FestivalImportService::class);
});

it('returns error when file does not exist or is empty', function () {
    $result = $this->service->import('/non/existent/path.csv');
    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors[0]['message'])->toContain('unreadable or does not exist');

    $emptyFile = UploadedFile::fake()->createWithContent('empty.csv', '');
    $emptyResult = $this->service->import($emptyFile);
    expect($emptyResult->hasErrors())->toBeTrue()
        ->and($emptyResult->errors[0]['message'])->toContain('CSV file is empty');
});

it('returns error when required headers are missing', function () {
    $badHeaderFile = UploadedFile::fake()->createWithContent('bad_headers.csv', "some_column,another_column\nval1,val2\n");
    $result = $this->service->import($badHeaderFile);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors[0]['message'])->toContain("Missing required 'name' column");
});

it('successfully imports and updates festivals and dates', function () {
    $csv = "name,category,date,days_before,days_after,target_specialties,greeting_message_template,adjustment_notes\n".
        "World Heart Day,clinical_observance,2026-09-29,2,1,\"Cardiology, Internal Medicine\",Heart health greetings,WHO health day\n".
        "World Heart Day,clinical_observance,2027-09-29,2,1,\"Cardiology, Internal Medicine\",Heart health greetings,WHO health day\n";

    $file = UploadedFile::fake()->createWithContent('heart_day.csv', $csv);
    $result = $this->service->import($file, $this->user->id);

    expect($result->isSuccessful())->toBeTrue()
        ->and($result->importedCount)->toBe(2);

    $festival = Festival::where('slug', 'world-heart-day')->first();
    expect($festival)->not->toBeNull()
        ->and($festival->target_specialties)->toBe(['Cardiology', 'Internal Medicine']);

    $dates = FestivalDate::where('festival_id', $festival->id)->get();
    expect($dates)->toHaveCount(2)
        ->and($dates->pluck('year')->all())->toEqual([2026, 2027])
        ->and($dates->first()->is_manually_adjusted)->toBeTrue()
        ->and($dates->first()->adjusted_by_user_id)->toBe($this->user->id);
});
