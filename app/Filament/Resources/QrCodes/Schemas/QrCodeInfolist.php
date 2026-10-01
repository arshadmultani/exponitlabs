<?php

namespace App\Filament\Resources\QrCodes\Schemas;

use App\Models\QrCode;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class QrCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('QR Code Overview')
                    ->columns(3)
                    ->components([
                        TextEntry::make('qr_preview')
                            ->label('QR Code')
                            ->state(fn (QrCode $record): string => $record->shortUrl())
                            ->formatStateUsing(function (string $state, QrCode $record): HtmlString {
                                $previewUrl = route('qr.preview', ['code' => $record->code]);
                                $pngUrl = route('qr.download', ['code' => $record->code, 'format' => 'png']);
                                $svgUrl = route('qr.download', ['code' => $record->code, 'format' => 'svg']);

                                return new HtmlString(
                                    '<div class="flex flex-col items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800 w-fit">'.
                                    '<img src="'.$previewUrl.'" alt="QR Preview" class="w-48 h-48 object-contain" />'.
                                    '<div class="flex gap-2 text-xs">'.
                                    '<a href="'.$pngUrl.'" target="_blank" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-700 font-medium dark:bg-gray-800 dark:text-gray-200">PNG</a>'.
                                    '<a href="'.$svgUrl.'" target="_blank" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded text-gray-700 font-medium dark:bg-gray-800 dark:text-gray-200">SVG</a>'.
                                    '</div>'.
                                    '</div>'
                                );
                            })
                            ->columnSpan(1),

                        Grid::make(2)
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Campaign Name')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),

                                TextEntry::make('short_url')
                                    ->label('Short Tracking URL')
                                    ->state(fn (QrCode $record): string => $record->shortUrl())
                                    ->copyable()
                                    ->columnSpan(1),

                                TextEntry::make('is_active')
                                    ->label('Campaign Status')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Paused')
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->columnSpan(1),

                                TextEntry::make('destination_url')
                                    ->label('Primary Destination URL')
                                    ->copyable()
                                    ->columnSpanFull(),

                                TextEntry::make('ios_url')
                                    ->label('iOS Fallback URL')
                                    ->placeholder('Default Destination')
                                    ->copyable()
                                    ->columnSpan(1),

                                TextEntry::make('android_url')
                                    ->label('Android Fallback URL')
                                    ->placeholder('Default Destination')
                                    ->copyable()
                                    ->columnSpan(1),

                                TextEntry::make('total_scans')
                                    ->label('Total Scans')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('unique_scans')
                                    ->label('Unique Visitors')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('last_scanned_at')
                                    ->label('Last Scanned At')
                                    ->since()
                                    ->placeholder('No scans recorded yet'),

                                TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime('M d, Y H:i'),
                            ]),
                    ]),
            ]);
    }
}
