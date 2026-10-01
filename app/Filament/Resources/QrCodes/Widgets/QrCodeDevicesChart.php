<?php

namespace App\Filament\Resources\QrCodes\Widgets;

use App\Models\QrCode;
use App\Models\QrScan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QrCodeDevicesChart extends ChartWidget
{
    public ?QrCode $record = null;

    protected ?string $heading = 'Scans by Device Type';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $scanQuery = $this->record
            ? $this->record->scans()
            : QrScan::query();

        $deviceCounts = (clone $scanQuery)
            ->whereNotNull('device_type')
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'mobile' => '#10b981',
            'desktop' => '#6366f1',
            'tablet' => '#f59e0b',
            'bot' => '#ef4444',
            'other' => '#94a3b8',
        ];

        $backgroundColors = [];

        foreach ($deviceCounts as $row) {
            $type = strtolower((string) $row->device_type);
            $labels[] = ucfirst($type);
            $data[] = (int) $row->count;
            $backgroundColors[] = $colors[$type] ?? '#94a3b8';
        }

        if (empty($labels)) {
            $labels = ['No Data'];
            $data = [1];
            $backgroundColors = ['#e2e8f0'];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
