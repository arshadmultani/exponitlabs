<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Models\Doctor;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Doctors\DoctorResource;

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
            ->recordUrl(fn (Doctor $record): string => DoctorResource::getUrl('view', ['record' => $record]))
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
                    ->toggleable()
                    ->default('NA')
                    ->searchable(),
                TextColumn::make('dcrs_count')
                    ->counts('dcrs')
                    ->label('DCRs')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->label('Added On')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
