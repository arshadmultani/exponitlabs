<?php

namespace App\Filament\Resources\QrCodes\RelationManagers;

use App\Models\QrScan;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScansRelationManager extends RelationManager
{
    protected static string $relationship = 'scans';

    protected static ?string $title = 'Individual Scan Logs';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ip_address')
            ->columns([
                TextColumn::make('scanned_at')
                    ->label('Scanned At')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->description(fn (QrScan $record): string => $record->scanned_at->diffForHumans()),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Unknown'),

                TextColumn::make('location')
                    ->label('Location')
                    ->state(fn (QrScan $record): string => trim(($record->city ? "{$record->city}, " : '').($record->country_name ?: ($record->country_code ?: '—'))))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('country_name', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    }),

                TextColumn::make('device_type')
                    ->label('Device')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mobile' => 'success',
                        'tablet' => 'warning',
                        'desktop' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('platform')
                    ->label('OS')
                    ->badge()
                    ->placeholder('Unknown'),

                TextColumn::make('browser')
                    ->label('Browser')
                    ->formatStateUsing(fn ($state, QrScan $record): string => trim("{$record->browser} {$record->browser_version}"))
                    ->placeholder('Unknown'),

                IconColumn::make('is_unique')
                    ->label('Unique')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedMinusCircle)
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->options([
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                        'desktop' => 'Desktop',
                        'bot' => 'Bot',
                    ]),

                SelectFilter::make('platform')
                    ->options([
                        'iOS' => 'iOS',
                        'Android' => 'Android',
                        'macOS' => 'macOS',
                        'Windows' => 'Windows',
                        'Linux' => 'Linux',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->schema([
                        Section::make('Scan Telemetry Details')
                            ->columns(2)
                            ->components([
                                TextEntry::make('scanned_at')
                                    ->label('Timestamp')
                                    ->dateTime('Y-m-d H:i:s'),

                                TextEntry::make('is_unique')
                                    ->label('First-Time Visit')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state ? 'Yes (Unique)' : 'No (Returning)'),

                                TextEntry::make('ip_address')
                                    ->label('IP (Anonymized)'),

                                TextEntry::make('country_name')
                                    ->label('Country')
                                    ->placeholder('Unknown'),

                                TextEntry::make('city')
                                    ->label('City')
                                    ->placeholder('Unknown'),

                                TextEntry::make('region')
                                    ->label('State / Region')
                                    ->placeholder('Unknown'),

                                TextEntry::make('device_type')
                                    ->label('Device Form Factor')
                                    ->badge(),

                                TextEntry::make('device_brand')
                                    ->label('Brand / Model')
                                    ->formatStateUsing(fn ($state, QrScan $record): string => trim("{$record->device_brand} {$record->device_model}") ?: '—'),

                                TextEntry::make('platform')
                                    ->label('Operating System')
                                    ->formatStateUsing(fn ($state, QrScan $record): string => trim("{$record->platform} {$record->platform_version}") ?: '—'),

                                TextEntry::make('browser')
                                    ->label('Browser / Client')
                                    ->formatStateUsing(fn ($state, QrScan $record): string => trim("{$record->browser} {$record->browser_version}") ?: '—'),

                                TextEntry::make('referrer')
                                    ->label('HTTP Referrer')
                                    ->placeholder('None (Direct Scan)')
                                    ->columnSpanFull(),

                                TextEntry::make('user_agent')
                                    ->label('Raw User Agent')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->defaultSort('scanned_at', 'desc');
    }
}
