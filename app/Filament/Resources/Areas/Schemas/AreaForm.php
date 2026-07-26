<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->helperText('Leave blank to generate from the name.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('headquarter_id')
                    ->relationship('headquarter', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
