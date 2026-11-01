<?php

namespace App\Filament\Widgets;

use App\Services\DoctorPortfolioQueryService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class DoctorPortfolioStatsWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public ?string $horizon = 'current_fy';

    #[On('portfolio-horizon-changed')]
    public function updateHorizon(string $horizon): void
    {
        $this->horizon = $horizon;
    }

    protected function getStats(): array
    {
        $queryService = app(DoctorPortfolioQueryService::class);
        [$startDate, $endDate] = $queryService->resolveHorizonRange($this->horizon ?? 'current_fy');
        $stats = $queryService->calculatePortfolioStats($startDate, $endDate);

        $isPositive = $stats['net_pnl'] >= 0.0;
        $pnlFormatted = ($isPositive ? '+₹' : '-₹').number_format(abs($stats['net_pnl']), 2);

        $returnMultiple = $stats['return_multiple'];
        $multipleColor = $returnMultiple >= 1.5 ? 'success' : ($returnMultiple >= 1.0 ? 'warning' : 'danger');

        return [
            Stat::make('Total Capital Invested', '₹'.number_format($stats['total_investment'], 2))
                ->description('Calls, samples & gifts')
                ->icon(Heroicon::Banknotes)
                ->color('gray'),

            Stat::make('Attributed Revenue', '₹'.number_format($stats['total_revenue'], 2))
                ->description('Prescription sales audits')
                ->icon(Heroicon::CurrencyRupee)
                ->color('info'),

            Stat::make('Net Commercial P&L', $pnlFormatted)
                ->description($isPositive ? 'Net positive return' : 'Net negative deficit')
                ->icon($isPositive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($isPositive ? 'success' : 'danger'),

            Stat::make('Overall Return Multiple', number_format($returnMultiple, 2).'x')
                ->description('Revenue / Detailing Investment')
                ->icon(Heroicon::ArrowTrendingUp)
                ->color($multipleColor),
        ];
    }
}
