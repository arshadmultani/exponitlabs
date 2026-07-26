<?php

namespace App\Filament\Resources\Doctors\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated([
                50, 100, 150, 200,
            ])
            ->defaultSort('name', 'asc')
            ->extremePaginationLinks()
            ->columns([
                // ImageColumn::make('profile_photo')
                //     ->label('Photo')
                //     ->disk('public')
                //     ->circular()
                //     ->height(40),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area.name')
                    ->label('Area')
                    ->badge()
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('town')
                    ->toggleable(),
                // IconColumn::make('has_microsite')
                //     ->label('Website')
                //     ->state(fn ($record) => $record->microsite()->exists())
                //     ->boolean(),

                // TextColumn::make('status')
                //     ->badge()
                //     ->colors([
                //         'success' => 'active',
                //         'gray' => 'inactive',
                //     ]),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->label('Added On')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
