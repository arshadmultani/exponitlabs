<?php

namespace App\Filament\Resources\TherapeuticAreas\Pages;

use App\Filament\Resources\TherapeuticAreas\TherapeuticAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTherapeuticArea extends EditRecord
{
    protected static string $resource = TherapeuticAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
