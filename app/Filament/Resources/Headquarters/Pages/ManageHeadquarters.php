<?php

namespace App\Filament\Resources\Headquarters\Pages;

use App\Filament\Resources\Headquarters\HeadquarterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHeadquarters extends ManageRecords
{
    protected static string $resource = HeadquarterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
