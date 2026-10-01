<?php

namespace App\Filament\Resources\QrCodes\Widgets;

use App\Models\QrCode;
use App\Models\QrScan;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QrCodeStatsOverview extends BaseWidget
{
    public ?QrCode $record = null;

    protected function getStats(): array
    {
        $scanQuery = $this->record
            ? $this->record->scans()
            : QrScan::query();

        $totalScans = $this->record
            ? $this->record->total_scans
            : (int) QrCode::sum('total_scans');

        $uniqueScans = $this->record
            ? $this->record->unique_scans
            : (int) QrCode::sum('unique_scans');

        $uniquePercent = $totalScans > 0
            ? round(($uniqueScans / $totalScans) * 100, 1)
            : 0;

        $topDevice = (clone $scanQuery)
            ->whereNotNull('device_type')
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->first();

        $topCountry = (clone $scanQuery)
            ->whereNotNull('country_name')
            ->select('country_name', DB::raw('count(*) as count'))
            ->groupBy('country_name')
            ->orderByDesc('count')
            ->first();

        return [
            Stat::make('Total Scans', number_format($totalScans))
                ->description('All time redirect clicks')
                ->descriptionIcon(Heroicon::OutlinedQrCode)
                ->color('primary'),

            Stat::make('Unique Visitors', number_format($uniqueScans))
                ->description("{$uniquePercent}% unique scan rate")
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('success'),

            Stat::make('Top Device', $topDevice ? ucfirst($topDevice->device_type) : 'None yet')
                ->description($topDevice ? "{$topDevice->count} scans" : 'Awaiting scans')
                ->descriptionIcon(Heroicon::OutlinedDevicePhoneMobile)
                ->color('info'),

            Stat::make('Top Location', $topCountry ? $topCountry->country_name : 'None yet')
                ->description($topCountry ? "{$topCountry->count} scans" : 'Awaiting scans')
                ->descriptionIcon(Heroicon::OutlinedGlobeAmericas)
                ->color('warning'),
        ];
    }
}
