<?php

namespace App\Filament\Resources\TherapeuticAreas\Pages;

use App\Filament\Resources\TherapeuticAreas\TherapeuticAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTherapeuticAreas extends ListRecords
{
    protected static string $resource = TherapeuticAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
