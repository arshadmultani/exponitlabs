<?php

namespace App\Services;

use App\DataTransferObjects\RevenueImportResult;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use SplFileObject;
use Throwable;

class DoctorRevenueImportService
{
    /**
     * Parse and import a monthly prescription revenue CSV.
     */
    public function import(UploadedFile|string $file, ?Carbon $fallbackPeriod = null, ?int $userId = null): RevenueImportResult
    {
        $result = new RevenueImportResult;

        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $result->addError(0, 'Revenue CSV file is unreadable or does not exist.');

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

        // Normalize header names (lowercase, trimmed, strip BOM)
        $header = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $col)));
        }, $header);

        $docIdIdx = array_search('doctor_id', $header, true);
        $amountIdx = array_search('amount', $header, true);
        $periodIdx = array_search('period_date', $header, true);
        if ($periodIdx === false) {
            $periodIdx = array_search('period', $header, true);
        }
        $notesIdx = array_search('notes', $header, true);

        if ($docIdIdx === false) {
            $result->addError(1, "Missing required 'doctor_id' column in CSV header.");
        }
        if ($amountIdx === false) {
            $result->addError(1, "Missing required 'amount' column in CSV header.");
        }

        if (! empty($result->errors)) {
            return $result;
        }

        // Prefetch doctor IDs for fast lookup
        $existingDoctorIds = Doctor::query()->pluck('id')->flip()->all();

        $rowsToInsert = [];
        $rowNumber = 1;

        while (! $csv->eof()) {
            $row = $csv->fgetcsv();
            $rowNumber++;

            if (! is_array($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            $rawDocId = trim((string) ($row[$docIdIdx] ?? ''));
            $rawAmount = trim((string) ($row[$amountIdx] ?? ''));
            $rawPeriod = $periodIdx !== false ? trim((string) ($row[$periodIdx] ?? '')) : '';
            $notes = $notesIdx !== false ? trim((string) ($row[$notesIdx] ?? '')) : null;

            // 1. Validate doctor ID
            if ($rawDocId === '' || ! ctype_digit($rawDocId)) {
                $result->addError($rowNumber, "Invalid doctor_id '{$rawDocId}': Must be an integer ID.");

                continue;
            }

            $doctorId = (int) $rawDocId;
            if (! isset($existingDoctorIds[$doctorId])) {
                $result->addError($rowNumber, "Doctor ID {$doctorId} does not exist in the database.");

                continue;
            }

            // 2. Validate amount
            if ($rawAmount === '' || ! is_numeric($rawAmount) || (float) $rawAmount <= 0.0) {
                $result->addError($rowNumber, "Invalid amount '{$rawAmount}': Must be a positive decimal.");

                continue;
            }
            $amount = round((float) $rawAmount, 2);

            // 3. Resolve and validate period date (normalized to YYYY-MM-01)
            $resolvedPeriod = null;
            if ($rawPeriod !== '') {
                try {
                    $resolvedPeriod = Carbon::parse($rawPeriod)->startOfMonth()->format('Y-m-d');
                } catch (Throwable) {
                    $result->addError($rowNumber, "Invalid period_date '{$rawPeriod}': Unable to parse date format.");

                    continue;
                }
            } elseif ($fallbackPeriod !== null) {
                $resolvedPeriod = $fallbackPeriod->copy()->startOfMonth()->format('Y-m-d');
            } else {
                $result->addError($rowNumber, 'Missing period_date: Provide period_date in CSV or select fallback period in modal.');

                continue;
            }

            $rowsToInsert[] = [
                'doctor_id' => $doctorId,
                'period_date' => $resolvedPeriod,
                'amount' => $amount,
                'source' => 'csv_import',
                'notes' => $notes !== '' ? $notes : null,
                'created_by_user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // If validation errors occurred, abort transaction and return failure report
        if (! empty($result->errors)) {
            $result->skippedCount = count($rowsToInsert);

            return $result;
        }

        // Commit valid records in atomic transaction
        if (! empty($rowsToInsert)) {
            DB::transaction(function () use ($rowsToInsert) {
                DoctorRevenue::insert($rowsToInsert);
            });

            $result->importedCount = count($rowsToInsert);
        }

        return $result;
    }
}
