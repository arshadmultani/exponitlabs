<?php

namespace App\Services;

use App\DataTransferObjects\FestivalImportResult;
use App\Models\Festival;
use App\Models\FestivalDate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SplFileObject;
use Throwable;

class FestivalImportService
{
    /**
     * Parse and import bulk festivals and observance dates from CSV.
     */
    public function import(UploadedFile|string $file, ?int $userId = null): FestivalImportResult
    {
        $result = new FestivalImportResult;

        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $result->addError(0, 'Festival CSV file is unreadable or does not exist.');

            return $result;
        }

        try {
            $csv = new SplFileObject($filePath, 'r');
            $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        } catch (Throwable $e) {
            $result->addError(0, 'Failed to open CSV: '.$e->getMessage());

            return $result;
        }

        $header = $csv->fgetcsv();
        if (! is_array($header) || empty($header)) {
            $result->addError(1, 'CSV file is empty or missing headers.');

            return $result;
        }

        // Normalize header names (lowercase, trimmed, strip non-ascii/BOM)
        $header = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $col)));
        }, $header);

        $nameIdx = array_search('name', $header, true);
        if ($nameIdx === false) {
            $nameIdx = array_search('festival_name', $header, true);
        }
        $dateIdx = array_search('date', $header, true);
        if ($dateIdx === false) {
            $dateIdx = array_search('observance_date', $header, true);
        }
        $categoryIdx = array_search('category', $header, true);
        $daysBeforeIdx = array_search('days_before', $header, true);
        $daysAfterIdx = array_search('days_after', $header, true);
        $specialtiesIdx = array_search('target_specialties', $header, true);
        if ($specialtiesIdx === false) {
            $specialtiesIdx = array_search('specialties', $header, true);
        }
        $templateIdx = array_search('greeting_message_template', $header, true);
        if ($templateIdx === false) {
            $templateIdx = array_search('greeting_template', $header, true);
        }
        $notesIdx = array_search('adjustment_notes', $header, true);
        if ($notesIdx === false) {
            $notesIdx = array_search('notes', $header, true);
        }

        if ($nameIdx === false) {
            $result->addError(1, "Missing required 'name' column in CSV header.");
        }
        if ($dateIdx === false) {
            $result->addError(1, "Missing required 'date' column in CSV header.");
        }

        if (! empty($result->errors)) {
            return $result;
        }

        $validRows = [];
        $rowNumber = 1;

        while (! $csv->eof()) {
            $row = $csv->fgetcsv();
            $rowNumber++;

            if (! is_array($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            $rawName = trim((string) ($row[$nameIdx] ?? ''));
            $rawDate = trim((string) ($row[$dateIdx] ?? ''));
            $rawCategory = $categoryIdx !== false ? trim((string) ($row[$categoryIdx] ?? '')) : '';
            $rawDaysBefore = $daysBeforeIdx !== false ? trim((string) ($row[$daysBeforeIdx] ?? '')) : '';
            $rawDaysAfter = $daysAfterIdx !== false ? trim((string) ($row[$daysAfterIdx] ?? '')) : '';
            $rawSpecialties = $specialtiesIdx !== false ? trim((string) ($row[$specialtiesIdx] ?? '')) : '';
            $rawTemplate = $templateIdx !== false ? trim((string) ($row[$templateIdx] ?? '')) : '';
            $rawNotes = $notesIdx !== false ? trim((string) ($row[$notesIdx] ?? '')) : '';

            // 1. Validate Name
            if ($rawName === '') {
                $result->addError($rowNumber, 'Festival/observance name cannot be empty.');

                continue;
            }

            // 2. Validate and Parse Date
            if ($rawDate === '') {
                $result->addError($rowNumber, "Missing date for festival '{$rawName}'.");

                continue;
            }

            try {
                $parsedDate = Carbon::parse($rawDate, FestivalGreetingService::TIMEZONE)->startOfDay();
            } catch (Throwable) {
                $result->addError($rowNumber, "Invalid date '{$rawDate}' for '{$rawName}': Must be a valid date format (e.g. YYYY-MM-DD).");

                continue;
            }

            // 3. Resolve Category
            $category = 'cultural_and_national';
            if ($rawCategory !== '') {
                $normalizedCategory = strtolower(str_replace([' ', '-'], '_', $rawCategory));
                if (in_array($normalizedCategory, ['clinical', 'clinical_observance', 'clinical_observances'], true)) {
                    $category = 'clinical_observance';
                } elseif (in_array($normalizedCategory, ['cultural', 'cultural_and_national', 'national'], true)) {
                    $category = 'cultural_and_national';
                } else {
                    $result->addError($rowNumber, "Invalid category '{$rawCategory}'. Must be 'cultural_and_national' or 'clinical_observance'.");

                    continue;
                }
            }

            // 4. Resolve Days Before & After
            $daysBefore = 2;
            if ($rawDaysBefore !== '') {
                if (! is_numeric($rawDaysBefore) || (int) $rawDaysBefore < 0) {
                    $result->addError($rowNumber, "Invalid days_before '{$rawDaysBefore}': Must be a non-negative integer.");

                    continue;
                }
                $daysBefore = (int) $rawDaysBefore;
            }

            $daysAfter = 1;
            if ($rawDaysAfter !== '') {
                if (! is_numeric($rawDaysAfter) || (int) $rawDaysAfter < 0) {
                    $result->addError($rowNumber, "Invalid days_after '{$rawDaysAfter}': Must be a non-negative integer.");

                    continue;
                }
                $daysAfter = (int) $rawDaysAfter;
            }

            // 5. Resolve Target Specialties
            $specialties = null;
            if ($category === 'clinical_observance' && $rawSpecialties !== '') {
                $parts = preg_split('/[;,]/', $rawSpecialties) ?: [];
                $specialties = array_values(array_filter(array_map('trim', $parts)));
            }

            $validRows[] = [
                'name' => $rawName,
                'date' => $parsedDate,
                'category' => $category,
                'days_before' => $daysBefore,
                'days_after' => $daysAfter,
                'target_specialties' => $specialties,
                'greeting_message_template' => $rawTemplate !== '' ? $rawTemplate : null,
                'adjustment_notes' => $rawNotes !== '' ? $rawNotes : 'Bulk CSV import',
            ];
        }

        if (! empty($result->errors)) {
            $result->skippedCount = count($validRows);

            return $result;
        }

        if (empty($validRows)) {
            $result->addError(1, 'No valid festival rows found to import.');

            return $result;
        }

        DB::transaction(function () use ($validRows, $userId, &$result) {
            foreach ($validRows as $row) {
                $baseSlug = Str::slug($row['name']);
                $slug = $baseSlug;

                $festival = Festival::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $row['name'],
                        'category' => $row['category'],
                        'days_before' => $row['days_before'],
                        'days_after' => $row['days_after'],
                        'target_specialties' => $row['target_specialties'],
                        'greeting_message_template' => $row['greeting_message_template'],
                        'is_active' => true,
                    ]
                );

                // Update festival metadata if changed
                $festival->update([
                    'category' => $row['category'],
                    'days_before' => $row['days_before'],
                    'days_after' => $row['days_after'],
                    'target_specialties' => $row['target_specialties'] ?? $festival->target_specialties,
                    'greeting_message_template' => $row['greeting_message_template'] ?? $festival->greeting_message_template,
                ]);

                $dateString = $row['date']->toDateString();
                $year = (int) $row['date']->year;

                // Create or update festival date
                $festivalDate = FestivalDate::where('festival_id', $festival->id)
                    ->where(function ($q) use ($year, $dateString) {
                        $q->where('year', $year)->orWhere('date', $dateString);
                    })
                    ->first();

                if ($festivalDate) {
                    $festivalDate->update([
                        'year' => $year,
                        'date' => $dateString,
                        'is_manually_adjusted' => true,
                        'adjusted_by_user_id' => $userId,
                        'adjustment_notes' => $row['adjustment_notes'],
                    ]);
                } else {
                    FestivalDate::create([
                        'festival_id' => $festival->id,
                        'year' => $year,
                        'date' => $dateString,
                        'is_manually_adjusted' => true,
                        'adjusted_by_user_id' => $userId,
                        'adjustment_notes' => $row['adjustment_notes'],
                    ]);
                }

                $result->incrementImported();
            }
        });

        Cache::forget('active_festivals_master_data');

        return $result;
    }
}
