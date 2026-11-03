<?php

namespace App\Filament\Resources\DoctorPotentials\Pages;

use App\Filament\Resources\DoctorPotentials\DoctorPotentialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDoctorPotentials extends ManageRecords
{
    protected static string $resource = DoctorPotentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
