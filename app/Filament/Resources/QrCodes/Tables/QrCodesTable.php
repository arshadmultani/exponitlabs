<?php

namespace App\Filament\Resources\QrCodes\Tables;

use App\Models\QrCode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class QrCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('preview')
                    ->label('QR Code')
                    ->state(fn (QrCode $record): string => $record->shortUrl())
                    ->formatStateUsing(function (string $state, QrCode $record): HtmlString {
                        $previewUrl = route('qr.preview', ['code' => $record->code]);

                        return new HtmlString(
                            '<div class="flex items-center justify-center p-1 bg-white border border-gray-200 rounded shadow-sm w-12 h-12 dark:border-gray-700">'.
                            '<img src="'.$previewUrl.'" alt="QR" class="w-10 h-10 object-contain" loading="lazy" />'.
                            '</div>'
                        );
                    }),

                TextColumn::make('name')
                    ->label('Name & Short Link')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (QrCode $record): string => $record->shortUrl())
                    ->copyable()
                    ->copyableState(fn (QrCode $record): string => $record->shortUrl()),

                TextColumn::make('destination_url')
                    ->label('Destination URL')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn (QrCode $record): string => $record->destination_url)
                    ->copyable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('total_scans')
                    ->label('Scans')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('unique_scans')
                    ->label('Unique')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('last_scanned_at')
                    ->label('Last Scanned')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All QR codes')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Action::make('changeUrl')
                    ->label('Change URL')
                    ->icon(Heroicon::OutlinedLink)
                    ->color('gray')
                    ->modalHeading(fn (QrCode $record): string => "Update Destination for '{$record->name}'")
                    ->modalDescription('Changes will apply instantly on the next scan without modifying the printed QR code.')
                    ->schema([
                        TextInput::make('destination_url')
                            ->label('New Destination URL')
                            ->url()
                            ->required()
                            ->default(fn (QrCode $record): string => $record->destination_url),
                    ])
                    ->action(function (array $data, QrCode $record): void {
                        $record->update(['destination_url' => $data['destination_url']]);
                    }),

                Action::make('download')
                    ->label('Download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Download QR Code')
                    ->modalDescription('Choose high-resolution PNG for digital use or scalable vector SVG for print and signage.')
                    ->modalActions([
                        Action::make('png')
                            ->label('Download PNG')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->url(fn (QrCode $record): string => route('qr.download', ['code' => $record->code, 'format' => 'png']))
                            ->openUrlInNewTab(),
                        Action::make('svg')
                            ->label('Download SVG (Vector)')
                            ->icon(Heroicon::OutlinedDocumentArrowDown)
                            ->url(fn (QrCode $record): string => route('qr.download', ['code' => $record->code, 'format' => 'svg']))
                            ->openUrlInNewTab(),
                    ]),

                ViewAction::make()
                    ->label('Analytics')
                    ->icon(Heroicon::OutlinedChartBar),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
