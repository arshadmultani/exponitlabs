<?php

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enquiry')
                    ->description('Submitted from the public contact form.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->label('Email address')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('organization')->disabled(),
                        Textarea::make('message')->rows(5)->disabled()->columnSpanFull(),
                    ]),

                Toggle::make('handled')
                    ->label('Marked as handled')
                    ->helperText('Tick once this enquiry has been followed up.'),
            ]);
    }
}
