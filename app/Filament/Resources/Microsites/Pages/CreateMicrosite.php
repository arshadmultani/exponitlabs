<?php

namespace App\Filament\Resources\Microsites\Pages;

use App\Filament\Resources\Microsites\MicrositeResource;
use App\Models\Doctor;
use App\Models\Microsite;
use Filament\Resources\Pages\CreateRecord;

class CreateMicrosite extends CreateRecord
{
    protected static string $resource = MicrositeResource::class;

    /**
     * Generate the public URL slug from the doctor's name at creation time.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $doctor = Doctor::find($data['doctor_id'] ?? null);
        $data['url'] = Microsite::uniqueUrl($doctor?->name ?? 'doctor');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
