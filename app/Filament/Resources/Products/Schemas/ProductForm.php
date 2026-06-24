<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->helperText('Leave blank to generate from the name.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('therapeutic_area_id')
                            ->label('Therapeutic area')
                            ->relationship('therapeuticArea', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('category')
                            ->placeholder('Tablet, Capsule, Syrup…'),

                        TextInput::make('composition')
                            ->columnSpanFull(),

                        TextInput::make('strength'),

                        TextInput::make('packaging'),

                        TextInput::make('mrp')
                            ->label('MRP (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->helperText('Used by the pricing calculator.'),

                        TextInput::make('unit_cost')
                            ->label('Unit cost (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->helperText('Company cost per unit (incl. GST). Optional.'),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Media & visibility')
                    ->columns(2)
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Product image')
                            ->image()
                            ->disk('public')
                            ->directory('products/images')
                            ->visibility('public')
                            ->maxSize(4 * 1024)
                            ->columnSpanFull(),

                        Toggle::make('is_featured')
                            ->label('Featured on homepage'),

                        Toggle::make('is_active')
                            ->label('Active (visible on site)')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
