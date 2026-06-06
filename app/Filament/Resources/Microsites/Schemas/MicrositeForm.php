<?php

namespace App\Filament\Resources\Microsites\Schemas;

use App\Models\Doctor;
use App\Models\Microsite;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MicrositeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor Website')
                    ->columns(2)
                    ->components([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor', 'name', fn ($query) => $query->active())
                            ->placeholder('Select doctor')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->optionsLimit(50)
                            ->noSearchResultsMessage('Doctor not found')
                            ->validationMessages([
                                'required' => 'Please select a doctor.',
                                'unique' => 'This doctor already has a website.',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if (! $state) {
                                    return;
                                }
                                $existing = Microsite::where('doctor_id', $state)->first();
                                if ($existing) {
                                    $doctor = Doctor::find($state);
                                    Notification::make()
                                        ->warning()
                                        ->title('Website already exists')
                                        ->body("A website for {$doctor?->name} already exists.")
                                        ->send();
                                }
                            }),

                        Toggle::make('is_active')
                            ->label('Active (publicly visible)')
                            ->default(true),

                        ColorPicker::make('design_settings.bg_color')
                            ->label('Background colour')
                            ->placeholder('#24669B')
                            ->helperText('Page background behind the white card. Leave empty for the default.'),
                    ]),
            ]);
    }
}
