<?php

namespace App\Filament\Resources\TherapeuticAreas\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TherapeuticAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->helperText('Leave blank to generate from the name.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('summary')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('icon')
                    ->label('Icon SVG path')
                    ->helperText('The "d" attribute of a 24×24 outline heroicon path. Optional.')
                    ->rows(2)
                    ->columnSpanFull(),

                ColorPicker::make('accent_color')
                    ->default('#1FB6AA'),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Active (visible on site)')
                    ->default(true),
            ]);
    }
}
