<?php

namespace App\Filament\Resources\ArCreatives\Tables;

use App\Support\Qr;
use App\Models\ArCreative;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArCreativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('marker_image_path')
                    ->label('Marker')
                    ->disk('public')
                    ->height(48),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                    ]),

                IconColumn::make('ready')
                    ->label('Ready')
                    ->tooltip('Has image, video and a compiled tracking file')
                    ->state(fn ($record) => $record->isReady())
                    ->boolean(),

                TextColumn::make('tracking_score')
                    ->label('Trackability')
                    ->badge()
                    ->formatStateUsing(fn ($state, ArCreative $record) => $record->trackabilityLabel())
                    ->color(fn ($state, ArCreative $record) => $record->trackabilityColor()),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('openAr')
                    ->label(fn (ArCreative $record) => $record->isPublished() ? 'Open AR' : 'Preview AR')
                    ->icon(Heroicon::OutlinedQrCode)
                    ->color('gray')
                    ->url(fn (ArCreative $record) => $record->arUrl())
                    ->openUrlInNewTab()
                    // Show whenever it can actually render. Drafts open as an admin
                    // preview (the controller allows signed-in users to view them).
                    ->visible(fn (ArCreative $record) => $record->isReady()),

                Action::make('downloadQr')
                    ->label('QR')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(function (ArCreative $record): StreamedResponse {
                        $result = Qr::forCreative($record);

                        return response()->streamDownload(
                            fn () => print ($result->getString()),
                            "qr-{$record->slug}.png",
                            ['Content-Type' => $result->getMimeType()],
                        );
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
