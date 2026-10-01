<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodes\QrCodeResource;
use App\Filament\Resources\QrCodes\Widgets\QrCodeDevicesChart;
use App\Filament\Resources\QrCodes\Widgets\QrCodeScansChart;
use App\Filament\Resources\QrCodes\Widgets\QrCodeStatsOverview;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewQrCode extends ViewRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPng')
                ->label('Download PNG')
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->url(fn (): string => route('qr.download', ['code' => $this->record->code, 'format' => 'png']))
                ->openUrlInNewTab(),

            Action::make('downloadSvg')
                ->label('Download SVG')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('gray')
                ->url(fn (): string => route('qr.download', ['code' => $this->record->code, 'format' => 'svg']))
                ->openUrlInNewTab(),

            EditAction::make(),
        ];
    }

    public function getHeading(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return "Tracking URL: {$this->record->shortUrl()} • Target: {$this->record->destination_url}";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QrCodeStatsOverview::class,
            QrCodeScansChart::class,
            QrCodeDevicesChart::class,
        ];
    }
}
