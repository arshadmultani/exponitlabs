<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Widgets\DoctorsMapWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDoctors extends ListRecords
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /** Map of all pinned doctors, rendered below the table. */
    protected function getFooterWidgets(): array
    {
        return [
            DoctorsMapWidget::class,
        ];
    }
}
