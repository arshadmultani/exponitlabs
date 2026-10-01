<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodes\QrCodeResource;
use App\Filament\Resources\QrCodes\Widgets\QrCodeStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQrCodes extends ListRecords
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Dynamic QR'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QrCodeStatsOverview::class,
        ];
    }
}
