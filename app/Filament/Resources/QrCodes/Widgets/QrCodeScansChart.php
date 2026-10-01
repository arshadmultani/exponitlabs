<?php

namespace App\Filament\Resources\QrCodes\Widgets;

use App\Models\QrCode;
use App\Models\QrScan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QrCodeScansChart extends ChartWidget
{
    public ?QrCode $record = null;

    protected ?string $heading = 'Scans Over Time (Last 14 Days)';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $startDate = now()->subDays(13)->startOfDay();
        $endDate = now()->endOfDay();

        $scanQuery = $this->record
            ? $this->record->scans()
            : QrScan::query();

        $rawScans = (clone $scanQuery)
            ->whereBetween('scanned_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(scanned_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when is_unique = 1 then 1 else 0 end) as uniques')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $totalData = [];
        $uniqueData = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $totalData[] = isset($rawScans[$date]) ? (int) $rawScans[$date]->total : 0;
            $uniqueData[] = isset($rawScans[$date]) ? (int) $rawScans[$date]->uniques : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Scans',
                    'data' => $totalData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Unique Visitors',
                    'data' => $uniqueData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
