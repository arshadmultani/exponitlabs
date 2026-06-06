<?php

namespace App\Filament\Resources\Doctors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->height(40),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('specialty')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('town')
                    ->toggleable(),

                IconColumn::make('has_microsite')
                    ->label('Website')
                    ->state(fn ($record) => $record->microsite()->exists())
                    ->boolean(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
