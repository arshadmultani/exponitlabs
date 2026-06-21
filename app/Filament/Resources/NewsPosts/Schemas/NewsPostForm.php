<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->helperText('Leave blank to generate from the title.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Textarea::make('excerpt')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        RichEditor::make('body')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->components([
                        FileUpload::make('cover_image_path')
                            ->label('Cover image')
                            ->image()
                            ->disk('public')
                            ->directory('news/covers')
                            ->visibility('public')
                            ->maxSize(4 * 1024)
                            ->columnSpanFull(),

                        DateTimePicker::make('published_at')
                            ->default(now()),

                        Toggle::make('is_published')
                            ->label('Published'),
                    ]),
            ]);
    }
}
