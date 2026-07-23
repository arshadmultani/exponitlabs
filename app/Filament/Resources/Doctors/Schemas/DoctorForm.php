<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Filament\Forms\Components\LocationMapField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),

                        TextInput::make('specialty')
                            ->maxLength(255)
                            ->placeholder('Cardiologist'),

                        TextInput::make('qualification')
                            ->maxLength(255)
                            ->placeholder('MBBS, MD'),

                        DatePicker::make('practice_since')
                            ->label('Practising since')
                            ->maxDate(now())
                            ->native(false),

                        TextInput::make('town')
                            ->maxLength(255),

                        Select::make('area_id')
                            ->label('Area')
                            ->relationship('area', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                            ]),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('clinic_name')
                            ->maxLength(255),

                        TextInput::make('address')
                            ->columnSpanFull()
                            ->maxLength(255),
                    ]),

                Section::make('Location')
                    ->description('Drag the pin or use your current location. Coordinates are saved automatically.')
                    ->components([
                        LocationMapField::make('location')
                            ->height(300),
                    ]),

                Section::make('Profile photo')
                    ->components([
                        FileUpload::make('profile_photo')
                            ->label('Profile photo')
                            ->image()
                            ->disk('public')
                            ->directory('doctors/profile_photos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->maxFiles(1)
                            ->helperText('Square image works best. Max 2 MB.'),
                    ]),
            ]);
    }
}
