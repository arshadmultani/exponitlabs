<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Festival;
use App\Models\FestivalDate;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FestivalGreetingService
{
    public const TIMEZONE = 'Asia/Kolkata';

    /**
     * Get active observances for a given evaluation date and optional physician specialty.
     *
     * An observance on date D is active on evaluation date T if:
     * D - days_before <= T <= D + days_after
     *
     * @return Collection<int, FestivalDate>
     */
    public function getActiveObservancesForDate(?Carbon $evalDate = null, ?string $specialty = null): Collection
    {
        $target = $evalDate
            ? ($evalDate->tzName === self::TIMEZONE
                ? $evalDate->copy()->startOfDay()
                : Carbon::parse($evalDate->format('Y-m-d'), self::TIMEZONE)->startOfDay())
            : Carbon::now(self::TIMEZONE)->startOfDay();

        $activeDates = FestivalDate::with('festival')
            ->whereIn('year', [$target->year - 1, $target->year, $target->year + 1])
            ->whereHas('festival', fn ($q) => $q->where('is_active', true))
            ->get()
            ->filter(function (FestivalDate $fd) use ($target, $specialty) {
                if (! $fd->festival || ! $fd->festival->is_active) {
                    return false;
                }

                $eventDate = Carbon::parse($fd->date->format('Y-m-d'), self::TIMEZONE)->startOfDay();
                $start = $eventDate->copy()->subDays($fd->festival->days_before)->startOfDay();
                $end = $eventDate->copy()->addDays($fd->festival->days_after)->endOfDay();

                if (! $target->between($start, $end)) {
                    return false;
                }

                if ($fd->festival->category === 'cultural_and_national') {
                    return true;
                }

                return $this->matchesSpecialty($specialty, $fd->festival->target_specialties ?? []);
            });

        return $activeDates->values();
    }

    /**
     * Match a physician's specialty against a festival's target specialties.
     *
     * @param  list<string>  $targetSpecialties
     */
    public function matchesSpecialty(?string $specialty, array $targetSpecialties): bool
    {
        if (! $specialty || empty($targetSpecialties)) {
            return false;
        }

        $doc = strtolower(trim($specialty));
        $normDoc = str_replace('paed', 'ped', $doc);

        $docTokens = array_filter(preg_split('/[\s,\-\/]+/', $normDoc) ?: []);

        foreach ($targetSpecialties as $target) {
            $tgt = strtolower(trim($target));
            $normTgt = str_replace('paed', 'ped', $tgt);

            // Direct substring containment on full strings
            if (str_contains($normDoc, $normTgt) || str_contains($normTgt, $normDoc)) {
                return true;
            }

            $targetTokens = array_filter(preg_split('/[\s,\-\/]+/', $normTgt) ?: []);

            // Check each word in doctor specialty against target words
            foreach ($docTokens as $docToken) {
                if (strlen($docToken) < 4) {
                    continue;
                }

                foreach ($targetTokens as $targetToken) {
                    if (strlen($targetToken) < 4) {
                        continue;
                    }

                    // Direct token containment
                    if (str_contains($docToken, $targetToken) || str_contains($targetToken, $docToken)) {
                        return true;
                    }

                    // Common medical root stem check (e.g. "cardi" in "cardiologist" & "cardiology")
                    $stemLen = min(5, strlen($docToken), strlen($targetToken));
                    if (substr($docToken, 0, $stemLen) === substr($targetToken, 0, $stemLen)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Generate a 42-cell calendar grid (6 rows x 7 days, Monday-anchored) for a given month.
     *
     * @return array<int, array{
     *     date: string,
     *     day: int,
     *     is_current_month: bool,
     *     is_today: bool,
     *     festivals: Collection<int, FestivalDate>,
     *     birthdays: Collection<int, Doctor>
     * }>
     */
    public function getMonthGridData(int $year, int $month, ?string $categoryFilter = null): array
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1, self::TIMEZONE)->startOfDay();
        $firstCellDate = $startOfMonth->copy()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
        $lastCellDate = $firstCellDate->copy()->addDays(41)->endOfDay();

        $todayStr = Carbon::now(self::TIMEZONE)->format('Y-m-d');

        $groupedDates = collect();
        if ($categoryFilter !== 'birthdays') {
            $query = FestivalDate::with('festival')
                ->where('date', '>=', $firstCellDate->toDateString())
                ->where('date', '<=', $lastCellDate->toDateString())
                ->whereHas('festival', function ($q) use ($categoryFilter) {
                    $q->where('is_active', true);
                    if ($categoryFilter && ! in_array($categoryFilter, ['all', 'birthdays'], true)) {
                        $q->where('category', $categoryFilter);
                    }
                });

            $groupedDates = $query->get()->groupBy(fn (FestivalDate $fd) => $fd->date->format('Y-m-d'));
        }

        $includeBirthdays = ($categoryFilter === 'all' || $categoryFilter === 'birthdays');
        $groupedBirthdays = collect();
        if ($includeBirthdays) {
            $doctorsWithBirthdays = Doctor::query()
                ->whereNotNull('date_of_birth')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'specialty', 'date_of_birth', 'phone', 'area_id']);

            $groupedBirthdays = $doctorsWithBirthdays->groupBy(fn (Doctor $doc) => $doc->date_of_birth->format('m-d'));
        }

        $grid = [];
        for ($i = 0; $i < 42; $i++) {
            $cellDate = $firstCellDate->copy()->addDays($i);
            $dateStr = $cellDate->format('Y-m-d');
            $monthDay = $cellDate->format('m-d');

            $birthdaysForCell = collect();
            if ($includeBirthdays) {
                $birthdaysForCell = $groupedBirthdays->get($monthDay, collect());
                // Handle Feb 29 leap-day birthdays in non-leap years
                if ($monthDay === '02-28' && ! $cellDate->isLeapYear()) {
                    $birthdaysForCell = $birthdaysForCell->merge($groupedBirthdays->get('02-29', collect()));
                }
            }

            $grid[] = [
                'date' => $dateStr,
                'day' => (int) $cellDate->format('j'),
                'is_current_month' => (int) $cellDate->format('n') === $month,
                'is_today' => $dateStr === $todayStr,
                'festivals' => $groupedDates->get($dateStr, collect()),
                'birthdays' => $birthdaysForCell,
            ];
        }

        return $grid;
    }

    /**
     * Create a new custom festival and its date for the specified year.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomFestival(array $data, ?int $userId = null): FestivalDate
    {
        $name = trim($data['name']);
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        while (Festival::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-".(++$counter);
        }

        $festival = Festival::create([
            'name' => $name,
            'slug' => $slug,
            'category' => $data['category'],
            'days_before' => $data['days_before'] ?? 2,
            'days_after' => $data['days_after'] ?? 1,
            'target_specialties' => $data['category'] === 'clinical_observance' ? ($data['target_specialties'] ?? []) : null,
            'greeting_message_template' => $data['greeting_message_template'] ?? null,
            'is_active' => true,
        ]);

        $date = Carbon::parse($data['date'], self::TIMEZONE)->startOfDay();

        $festivalDate = FestivalDate::create([
            'festival_id' => $festival->id,
            'year' => (int) $date->year,
            'date' => $date->toDateString(),
            'is_manually_adjusted' => true,
            'adjusted_by_user_id' => $userId,
            'adjustment_notes' => $data['adjustment_notes'] ?? 'Custom festival manually created',
        ]);

        Cache::forget('active_festivals_master_data');

        return $festivalDate;
    }

    /**
     * Retrieve canonical baseline date from database/data/indian_festivals.json.
     */
    public function getFestivalDateBaseline(FestivalDate $festivalDate): ?string
    {
        $festival = $festivalDate->festival;
        if (! $festival) {
            return null;
        }

        $filePath = database_path('data/indian_festivals.json');
        if (! File::exists($filePath)) {
            return null;
        }

        $items = json_decode(File::get($filePath), true);
        if (! is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (($item['slug'] ?? null) === $festival->slug) {
                return $item['dates'][(string) $festivalDate->year] ?? null;
            }
        }

        return null;
    }

    /**
     * Reset a manually adjusted date back to the canonical JSON baseline.
     */
    public function resetFestivalDateToBaseline(FestivalDate $festivalDate): bool
    {
        $baselineDate = $this->getFestivalDateBaseline($festivalDate);
        if (! $baselineDate) {
            return false;
        }

        $festivalDate->update([
            'date' => $baselineDate,
            'is_manually_adjusted' => false,
            'adjusted_by_user_id' => null,
            'adjustment_notes' => null,
        ]);

        Cache::forget('active_festivals_master_data');

        return true;
    }

    /**
     * Apply a manual date adjustment to a festival date.
     */
    public function adjustFestivalDate(FestivalDate $festivalDate, string $newDate, ?string $notes = null, ?int $userId = null): bool
    {
        $festivalDate->update([
            'date' => $newDate,
            'is_manually_adjusted' => true,
            'adjusted_by_user_id' => $userId,
            'adjustment_notes' => $notes,
        ]);

        Cache::forget('active_festivals_master_data');

        return true;
    }
}
