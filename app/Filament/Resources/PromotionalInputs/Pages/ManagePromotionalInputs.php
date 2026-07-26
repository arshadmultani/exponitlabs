<?php

namespace App\Filament\Resources\PromotionalInputs\Pages;

use App\Filament\Resources\PromotionalInputs\PromotionalInputResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePromotionalInputs extends ManageRecords
{
    protected static string $resource = PromotionalInputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
