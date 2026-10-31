<?php

use App\Models\Doctor;
use App\Models\DoctorRevenue;
use App\Models\User;
use App\Services\DoctorRevenueImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('imports valid CSV with doctor_id, period_date, amount, and notes', function () {
    $doctor1 = Doctor::factory()->create();
    $doctor2 = Doctor::factory()->create();
    $user = User::factory()->create();

    $csvContent = implode("\n", [
        'doctor_id,period_date,amount,notes',
        "{$doctor1->id},2026-10-01,15000.00,Apollo Pharmacy stockist audit",
        "{$doctor2->id},2026-10-01,8500.50,MedPlus retail chemist sales",
    ]);

    $file = UploadedFile::fake()->createWithContent('revenue_oct_2026.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $result = $service->import($file, null, $user->id);

    expect($result->isSuccessful())->toBeTrue()
        ->and($result->importedCount)->toBe(2)
        ->and($result->errors)->toBeEmpty();

    expect(DoctorRevenue::where('doctor_id', $doctor1->id)->count())->toBe(1)
        ->and((float) DoctorRevenue::where('doctor_id', $doctor1->id)->first()->amount)->toBe(15000.00)
        ->and(DoctorRevenue::where('doctor_id', $doctor2->id)->count())->toBe(1)
        ->and(DoctorRevenue::where('doctor_id', $doctor2->id)->first()->source)->toBe('csv_import');
});

it('uses fallback period when period_date is omitted from CSV header or row', function () {
    $doctor = Doctor::factory()->create();

    $csvContent = implode("\n", [
        'doctor_id,amount,notes',
        "{$doctor->id},12000.00,City Clinic Sales",
    ]);

    $file = UploadedFile::fake()->createWithContent('revenue.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $fallbackDate = Carbon::parse('2026-11-20');
    $result = $service->import($file, $fallbackDate);

    expect($result->isSuccessful())->toBeTrue()
        ->and($result->importedCount)->toBe(1);

    $revenue = DoctorRevenue::where('doctor_id', $doctor->id)->first();
    expect($revenue->period_date->format('Y-m-d'))->toBe('2026-11-01');
});

it('supports multi-chemist sales audits for the same doctor in the same period without collision', function () {
    $doctor = Doctor::factory()->create();

    $csvContent = implode("\n", [
        'doctor_id,period_date,amount,notes',
        "{$doctor->id},2026-10-01,4000.00,Chemist Alpha",
        "{$doctor->id},2026-10-01,6000.00,Chemist Beta",
    ]);

    $file = UploadedFile::fake()->createWithContent('multi_chemist.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $result = $service->import($file);

    expect($result->isSuccessful())->toBeTrue()
        ->and($result->importedCount)->toBe(2);

    expect(DoctorRevenue::where('doctor_id', $doctor->id)->count())->toBe(2)
        ->and((float) DoctorRevenue::where('doctor_id', $doctor->id)->sum('amount'))->toBe(10000.00);
});

it('rejects CSV with missing required columns', function () {
    $csvContent = "random_header,notes\n1,some notes";
    $file = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $result = $service->import($file);

    expect($result->isSuccessful())->toBeFalse()
        ->and($result->errors)->toHaveCount(2); // Missing doctor_id and amount
});

it('rejects non-existent doctor IDs with row-level validation errors and rolls back', function () {
    $validDoctor = Doctor::factory()->create();
    $nonExistentId = 999999;

    $csvContent = implode("\n", [
        'doctor_id,period_date,amount,notes',
        "{$validDoctor->id},2026-10-01,5000.00,Valid Row",
        "{$nonExistentId},2026-10-01,3000.00,Invalid Row",
    ]);

    $file = UploadedFile::fake()->createWithContent('partial_invalid.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $result = $service->import($file);

    expect($result->isSuccessful())->toBeFalse()
        ->and($result->importedCount)->toBe(0)
        ->and($result->errors)->toHaveCount(1)
        ->and($result->errors[0]['row'])->toBe(3)
        ->and($result->errors[0]['message'])->toContain("Doctor ID {$nonExistentId} does not exist");

    // All-or-nothing: ensure valid row was not inserted
    expect(DoctorRevenue::count())->toBe(0);
});

it('rejects invalid or zero amounts with row-level errors', function () {
    $doctor = Doctor::factory()->create();

    $csvContent = implode("\n", [
        'doctor_id,period_date,amount,notes',
        "{$doctor->id},2026-10-01,-150.00,Negative amount",
        "{$doctor->id},2026-10-01,not_a_number,Non-numeric amount",
    ]);

    $file = UploadedFile::fake()->createWithContent('invalid_amounts.csv', $csvContent);

    $service = new DoctorRevenueImportService;
    $result = $service->import($file);

    expect($result->isSuccessful())->toBeFalse()
        ->and($result->errors)->toHaveCount(2)
        ->and(DoctorRevenue::count())->toBe(0);
});
