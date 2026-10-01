<?php

namespace App\Filament\Resources\QrCodes\Schemas;

use App\Models\QrCode;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign & Target URL')
                    ->description('Set your dynamic redirect link. You can change this link at any time without reprinting the QR code.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Campaign Name')
                            ->placeholder('e.g. Summer Exhibition Standee')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label('Short Code')
                            ->helperText('Custom slug for the tracking URL (/q/{code}). Generated automatically if left blank.')
                            ->maxLength(32)
                            ->unique(QrCode::class, 'code', ignoreRecord: true)
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Active (Accept Scans)')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),

                        TextInput::make('destination_url')
                            ->label('Primary Destination URL')
                            ->placeholder('https://example.com/landing-page')
                            ->url()
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->helperText('Where users will be redirected immediately upon scanning.'),
                    ]),

                Section::make('Smart Device Routing')
                    ->description('Optionally redirect users to different destinations based on their device operating system.')
                    ->collapsed()
                    ->columns(2)
                    ->components([
                        TextInput::make('ios_url')
                            ->label('iOS Destination (App Store / Deep Link)')
                            ->placeholder('https://apps.apple.com/app/...')
                            ->url()
                            ->maxLength(1000)
                            ->helperText('iPhone and iPad scanners will be redirected here instead.'),

                        TextInput::make('android_url')
                            ->label('Android Destination (Play Store / Deep Link)')
                            ->placeholder('https://play.google.com/store/apps/...')
                            ->url()
                            ->maxLength(1000)
                            ->helperText('Android scanners will be redirected here instead.'),
                    ]),

                Section::make('Limits & Expiration')
                    ->description('Schedule when this QR code should stop accepting scans, or set a maximum scan threshold.')
                    ->collapsed()
                    ->columns(3)
                    ->components([
                        DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->native(false)
                            ->helperText('No redirects after this time.'),

                        TextInput::make('max_scans')
                            ->label('Scan Limit')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Disable after reaching this many total scans.'),

                        TextInput::make('fallback_url')
                            ->label('Expired / Fallback URL')
                            ->placeholder('https://example.com/campaign-ended')
                            ->url()
                            ->maxLength(1000)
                            ->helperText('Where to send users if this QR is paused or expired.'),
                    ]),

                Section::make('Design & Branding')
                    ->description('Customize colors, add your brand logo in the center, and set caption labels.')
                    ->collapsed()
                    ->columns(2)
                    ->components([
                        ColorPicker::make('design.foreground_color')
                            ->label('QR Pattern Color')
                            ->default('#0f172a'),

                        ColorPicker::make('design.background_color')
                            ->label('Background Color')
                            ->default('#ffffff'),

                        Select::make('design.error_correction')
                            ->label('Error Correction Level')
                            ->options([
                                'low' => 'Low (~7% recovery)',
                                'medium' => 'Medium (~15% recovery)',
                                'quartile' => 'Quartile (~25% recovery)',
                                'high' => 'High (~30% recovery - Recommended with Logo)',
                            ])
                            ->default('high')
                            ->native(false),

                        TextInput::make('design.label_text')
                            ->label('Label Caption (Below QR)')
                            ->placeholder('e.g. Scan to connect')
                            ->maxLength(60),

                        FileUpload::make('design.logo_path')
                            ->label('Center Logo')
                            ->image()
                            ->disk('public')
                            ->directory('qr/logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->columnSpanFull()
                            ->helperText('A crisp PNG or SVG logo displayed in the center of the QR matrix.'),
                    ]),
            ]);
    }
}
