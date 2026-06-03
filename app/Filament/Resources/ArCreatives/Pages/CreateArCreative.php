<?php

namespace App\Filament\Resources\ArCreatives\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ArCreatives\ArCreativeResource;

class CreateArCreative extends CreateRecord
{
    protected static string $resource = ArCreativeResource::class;

    /**
     * You can't publish before compiling, and compiling only happens on the edit
     * screen — so a brand-new creative is always forced to draft.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === 'published') {
            $data['status'] = 'draft';

            Notification::make()
                ->warning()
                ->title('Saved as draft')
                ->body('Compile the tracking file on the edit screen, then publish.')
                ->send();
        }

        return $data;
    }
}
