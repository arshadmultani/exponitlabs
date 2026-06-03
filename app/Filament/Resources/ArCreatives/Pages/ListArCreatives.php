<?php

namespace App\Filament\Resources\ArCreatives\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ArCreatives\ArCreativeResource;

class ListArCreatives extends ListRecords
{
    protected static string $resource = ArCreativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
