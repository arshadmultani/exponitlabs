<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
            ]);
    }
}
