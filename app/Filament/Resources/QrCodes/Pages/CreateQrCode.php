<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodes\QrCodeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateQrCode extends CreateRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
