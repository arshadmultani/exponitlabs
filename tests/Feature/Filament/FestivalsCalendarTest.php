<?php

use App\Filament\Pages\FestivalsCalendar;
use App\Models\Doctor;
use App\Models\Festival;
use App\Models\FestivalDate;
use App\Models\User;
use App\Services\FestivalGreetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the festival calendar page in console for authenticated users', function () {
    $this->get('/console/festivals')
        ->assertSuccessful()
        ->assertSee('Festival Calendar')
        ->assertSee('Indian Festivals & Healthcare Observances');
});

it('navigates months and updates grid view state', function () {
    $component = Livewire::test(FestivalsCalendar::class)
        ->assertSet('year', (int) now()->year)
        ->assertSet('month', (int) now()->month)
        ->call('nextMonth');

    $next = Carbon::createFromDate(now()->year, now()->month, 1)->addMonth();
    $component->assertSet('year', (int) $next->year)
        ->assertSet('month', (int) $next->month)
        ->call('previousMonth')
        ->assertSet('year', (int) now()->year)
        ->assertSet('month', (int) now()->month)
        ->call('goToToday')
        ->assertSet('year', (int) now()->year)
        ->assertSet('month', (int) now()->month);
});

it('filters calendar by category pills', function () {
    Livewire::test(FestivalsCalendar::class)
        ->assertSet('categoryFilter', 'all')
        ->call('setCategoryFilter', 'cultural_and_national')
        ->assertSet('categoryFilter', 'cultural_and_national')
        ->call('setCategoryFilter', 'clinical_observance')
        ->assertSet('categoryFilter', 'clinical_observance');
});

it('edits a festival date via editFestivalDate action and marks it manually adjusted', function () {
    Artisan::call('festivals:import');

    $eid = Festival::where('slug', 'eid-ul-fitr')->firstOrFail();
    $eidDate = FestivalDate::where('festival_id', $eid->id)->where('year', 2025)->firstOrFail();

    expect($eidDate->date->format('Y-m-d'))->toBe('2025-03-31')
        ->and($eidDate->is_manually_adjusted)->toBeFalse();

    Livewire::test(FestivalsCalendar::class)
        ->callAction('editFestivalDate', [
            'date' => '2025-04-01',
            'adjustment_notes' => 'Moon sighting delayed by 1 day',
        ], [
            'festivalDateId' => $eidDate->id,
        ])
        ->assertNotified('Festival date updated successfully.');

    $eidDate->refresh();
    expect($eidDate->date->format('Y-m-d'))->toBe('2025-04-01')
        ->and($eidDate->is_manually_adjusted)->toBeTrue()
        ->and($eidDate->adjustment_notes)->toBe('Moon sighting delayed by 1 day')
        ->and($eidDate->adjusted_by_user_id)->toBe($this->user->id);
});

it('resets a manually adjusted festival date back to the official baseline', function () {
    Artisan::call('festivals:import');

    $eid = Festival::where('slug', 'eid-ul-fitr')->firstOrFail();
    $eidDate = FestivalDate::where('festival_id', $eid->id)->where('year', 2025)->firstOrFail();

    // Mark as manually adjusted
    $eidDate->update([
        'date' => '2025-04-01',
        'is_manually_adjusted' => true,
        'adjustment_notes' => 'Custom shifted date',
        'adjusted_by_user_id' => $this->user->id,
    ]);

    Livewire::test(FestivalsCalendar::class)
        ->callAction('resetToOfficialDate', [], [
            'festivalDateId' => $eidDate->id,
        ])
        ->assertNotified('Restored official gazetted date.');

    $eidDate->refresh();
    expect($eidDate->date->format('Y-m-d'))->toBe('2025-03-31')
        ->and($eidDate->is_manually_adjusted)->toBeFalse()
        ->and($eidDate->adjustment_notes)->toBeNull()
        ->and($eidDate->adjusted_by_user_id)->toBeNull();
});

it('triggers official dataset re-sync from header action', function () {
    Livewire::test(FestivalsCalendar::class)
        ->callAction('syncOfficialDataset')
        ->assertNotified('Official festivals dataset synchronized.');

    expect(Festival::count())->toBe(29)
        ->and(FestivalDate::count())->toBe(174);
});

it('creates a custom festival manually via header action', function () {
    Livewire::test(FestivalsCalendar::class)
        ->callAction('createFestival', [
            'name' => 'Kolkata Foundation Day',
            'category' => 'cultural_and_national',
            'date' => '2026-08-24',
            'days_before' => 2,
            'days_after' => 1,
            'greeting_message_template' => 'Happy Kolkata Day Dr. {doctor_name}',
            'adjustment_notes' => 'City observance',
        ])
        ->assertNotified("Observance 'Kolkata Foundation Day' created successfully.");

    $festival = Festival::where('name', 'Kolkata Foundation Day')->first();
    expect($festival)->not->toBeNull()
        ->and($festival->category)->toBe('cultural_and_national')
        ->and($festival->days_before)->toBe(2)
        ->and($festival->days_after)->toBe(1);

    $date = FestivalDate::where('festival_id', $festival->id)->first();
    expect($date)->not->toBeNull()
        ->and($date->date->format('Y-m-d'))->toBe('2026-08-24')
        ->and($date->year)->toBe(2026)
        ->and($date->is_manually_adjusted)->toBeTrue()
        ->and($date->adjustment_notes)->toBe('City observance')
        ->and($date->adjusted_by_user_id)->toBe($this->user->id);
});

it('supports create and create another on manual festival creation', function () {
    Livewire::test(FestivalsCalendar::class)
        ->callAction('createFestival', [
            'name' => 'Custom Clinical Health Day',
            'category' => 'clinical_observance',
            'date' => '2026-09-15',
            'days_before' => 1,
            'days_after' => 1,
            'target_specialties' => ['Cardiology', 'Pediatrics'],
        ], ['another' => true])
        ->assertActionHalted('createFestival')
        ->assertNotified("Observance 'Custom Clinical Health Day' created successfully.");

    $festival = Festival::where('name', 'Custom Clinical Health Day')->first();
    expect($festival)->not->toBeNull()
        ->and($festival->category)->toBe('clinical_observance')
        ->and($festival->target_specialties)->toBe(['Cardiology', 'Pediatrics']);
});

it('displays doctor birthdays on the calendar grid and allows filtering', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Rakesh Patil',
        'date_of_birth' => '1982-11-14',
        'status' => 'active',
    ]);

    $service = app(FestivalGreetingService::class);

    // Month grid for November 2026
    $grid = $service->getMonthGridData(2026, 11, 'all');

    $nov14Cell = collect($grid)->firstWhere('date', '2026-11-14');
    expect($nov14Cell)->not->toBeNull()
        ->and($nov14Cell['birthdays']->pluck('id'))->toContain($doctor->id);

    // Filter by birthdays
    $birthdayGrid = $service->getMonthGridData(2026, 11, 'birthdays');
    $nov14BirthdayCell = collect($birthdayGrid)->firstWhere('date', '2026-11-14');
    expect($nov14BirthdayCell['birthdays']->pluck('id'))->toContain($doctor->id)
        ->and($nov14BirthdayCell['festivals'])->toBeEmpty();

    // Filter by cultural_and_national hides birthdays
    $culturalGrid = $service->getMonthGridData(2026, 11, 'cultural_and_national');
    $nov14CulturalCell = collect($culturalGrid)->firstWhere('date', '2026-11-14');
    expect($nov14CulturalCell['birthdays'])->toBeEmpty();
});

it('opens doctor birthday modal via viewDoctorBirthday action', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. Birthday View',
        'phone' => '9876543210',
        'email' => 'drview@example.com',
        'clinic_name' => 'City Care Clinic',
        'date_of_birth' => '1985-06-10',
    ]);

    Livewire::test(FestivalsCalendar::class)
        ->callAction('viewDoctorBirthday', [], [
            'doctorId' => $doctor->id,
        ])
        ->assertHasNoActionErrors();
});

it('downloads festival import template via header action', function () {
    $response = Livewire::test(FestivalsCalendar::class)
        ->callAction('downloadTemplate');

    $response->assertFileDownloaded('festivals_template.csv');
});

it('bulk imports festivals via CSV upload header action', function () {
    Storage::fake('local');

    $csvContent = "name,category,date,days_before,days_after,target_specialties,greeting_message_template,adjustment_notes\n".
        "Kolkata Durga Carnival,cultural_and_national,2026-10-22,3,1,,Joyous Durga Puja wishes!,State carnival\n".
        "National Epilepsy Day,clinical_observance,2026-11-17,1,1,\"Neurology;Pediatrics\",Saluting your care on Epilepsy Day,National health day\n";

    $file = UploadedFile::fake()->createWithContent('festivals.csv', $csvContent);

    Livewire::test(FestivalsCalendar::class)
        ->callAction('importFestivals', [
            'csv_file' => [$file],
        ])
        ->assertNotified('Successfully imported 2 festival observances');

    $cultural = Festival::where('name', 'Kolkata Durga Carnival')->first();
    expect($cultural)->not->toBeNull()
        ->and($cultural->category)->toBe('cultural_and_national')
        ->and($cultural->days_before)->toBe(3);

    $culturalDate = FestivalDate::where('festival_id', $cultural->id)->first();
    expect($culturalDate)->not->toBeNull()
        ->and($culturalDate->date->format('Y-m-d'))->toBe('2026-10-22')
        ->and($culturalDate->year)->toBe(2026)
        ->and($culturalDate->is_manually_adjusted)->toBeTrue()
        ->and($culturalDate->adjustment_notes)->toBe('State carnival');

    $clinical = Festival::where('name', 'National Epilepsy Day')->first();
    expect($clinical)->not->toBeNull()
        ->and($clinical->category)->toBe('clinical_observance')
        ->and($clinical->target_specialties)->toBe(['Neurology', 'Pediatrics']);
});

it('reports validation errors when importing malformed festival CSV', function () {
    Storage::fake('local');

    // Row 2 is missing date, row 3 has invalid category
    $csvContent = "name,category,date\n".
        "Festival Without Date,cultural_and_national,\n".
        "Festival With Bad Category,alien_category,2026-05-10\n";

    $file = UploadedFile::fake()->createWithContent('bad_festivals.csv', $csvContent);

    Livewire::test(FestivalsCalendar::class)
        ->callAction('importFestivals', [
            'csv_file' => [$file],
        ])
        ->assertNotified('Import stopped with 2 issues');
});
