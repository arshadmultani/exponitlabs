<?php

namespace App\Console\Commands;

use App\Models\Festival;
use App\Models\FestivalDate;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

#[Signature('festivals:import {--file= : Path to JSON file (defaults to database/data/indian_festivals.json)} {--force : Force overwrite festival metadata} {--force-reset-overrides : Force reset manual date adjustments to JSON baseline}')]
#[Description('Import Indian cultural, national, and clinical observance calendar data (2025–2030)')]
class ImportIndianFestivalsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->option('file')
            ? (string) $this->option('file')
            : database_path('data/indian_festivals.json');

        if (! File::exists($filePath)) {
            $this->components->error("Festivals JSON file not found at: {$filePath}");

            return self::FAILURE;
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (! is_array($data)) {
            $this->components->error("Invalid JSON format in: {$filePath}");

            return self::FAILURE;
        }

        $forceResetOverrides = (bool) $this->option('force-reset-overrides');

        $this->components->info(sprintf(
            'Importing %d festivals from %s (Reset Overrides: %s)...',
            count($data),
            basename($filePath),
            $forceResetOverrides ? 'YES' : 'NO'
        ));

        $festivalsImported = 0;
        $datesImported = 0;
        $overridesSkipped = 0;

        foreach ($data as $item) {
            $festival = Festival::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'] ?? 'cultural_and_national',
                    'target_specialties' => $item['target_specialties'] ?? null,
                    'days_before' => $item['days_before'] ?? 3,
                    'days_after' => $item['days_after'] ?? 1,
                    'greeting_message_template' => $item['greeting_message_template'] ?? null,
                    'is_active' => $item['is_active'] ?? true,
                ]
            );

            $festivalsImported++;

            if (! empty($item['dates']) && is_array($item['dates'])) {
                foreach ($item['dates'] as $year => $dateStr) {
                    $yearInt = (int) $year;

                    $existingDate = FestivalDate::where('festival_id', $festival->id)
                        ->where(function ($query) use ($yearInt, $dateStr) {
                            $query->where('year', $yearInt)
                                ->orWhere('date', $dateStr);
                        })
                        ->first();

                    if ($existingDate) {
                        if ($existingDate->is_manually_adjusted && ! $forceResetOverrides) {
                            $overridesSkipped++;

                            continue;
                        }

                        $existingDate->update([
                            'year' => $yearInt,
                            'date' => $dateStr,
                            'is_manually_adjusted' => false,
                            'adjusted_by_user_id' => null,
                            'adjustment_notes' => null,
                        ]);
                        $datesImported++;
                    } else {
                        FestivalDate::create([
                            'festival_id' => $festival->id,
                            'year' => $yearInt,
                            'date' => $dateStr,
                            'is_manually_adjusted' => false,
                            'adjusted_by_user_id' => null,
                            'adjustment_notes' => null,
                        ]);
                        $datesImported++;
                    }
                }
            }
        }

        Cache::forget('active_festivals_master_data');

        $this->components->info(sprintf(
            'Import complete: %d festivals synced, %d dates saved, %d manual overrides preserved.',
            $festivalsImported,
            $datesImported,
            $overridesSkipped
        ));

        return self::SUCCESS;
    }
}
