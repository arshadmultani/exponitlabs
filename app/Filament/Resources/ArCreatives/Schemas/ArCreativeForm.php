<?php

namespace App\Filament\Resources\ArCreatives\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class ArCreativeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Creative')
                    ->description('Give this AR creative a name and decide whether doctors can see it yet.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft (hidden)',
                                'published' => 'Published (live)',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),

                        Select::make('play_mode')
                            ->label('Video playback')
                            ->options([
                                'loop' => 'Loop while on the image',
                                'once' => 'Play once',
                            ])
                            ->default('loop')
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Marker image & video')
                    ->description('Upload the printed creative image (what the camera locks onto) and the video that plays on top of it.')
                    ->columns(2)
                    ->components([
                        FileUpload::make('marker_image_path')
                            ->label('Marker image')
                            ->helperText('The printed picture the doctor points the camera at. Use a detailed, high-contrast image for reliable tracking.')
                            ->image()
                            ->disk('public')
                            ->directory('ar/markers')
                            ->visibility('public')
                            ->maxSize(8 * 1024) // 8 MB
                            ->required(),

                        FileUpload::make('video_path')
                            ->label('Video')
                            ->helperText('The .mp4 that plays on the image. Keep it small (compressed) so it loads fast on mobile data.')
                            ->disk('public')
                            ->directory('ar/videos')
                            ->visibility('public')
                            ->acceptedFileTypes(['video/mp4'])
                            ->maxSize(50 * 1024) // 50 MB
                            ->previewable(false) // don't download the whole video just to preview it
                            ->required(),
                    ]),

                Section::make('AR tracking file')
                    ->description('Turn the marker image into a tracking file. This runs in your browser — nothing is sent to any outside service.')
                    ->visibleOn('edit')
                    ->components([
                        View::make('filament.ar-compiler'),
                    ]),
            ]);
    }
}
