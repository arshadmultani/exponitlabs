<?php

namespace App\Filament\Resources\DCRS;

use App\Filament\Resources\DCRS\Pages\ManageDCRS;
use App\Models\DCR;
use App\Models\Product;
use App\Models\PromotionalInput;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DCRResource extends Resource
{
    protected static ?string $model = DCR::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $modelLabel = 'DCR';

    protected static ?string $pluralModelLabel = 'DCR';

    protected static ?string $slug = 'dcrs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->default(now()->toDateString()),
                Select::make('doctor_id')
                    ->label('Doctor')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Checkbox::make('sample_given')
                    ->label('Samples given?')
                    ->live()
                    ->dehydrated(false),

                Section::make('Sample Products')
                    ->visible(fn (Get $get) => $get->boolean('sample_given'))
                    ->schema(
                        fn () => Product::query()->orderBy('name')
                            ->get()
                            ->map(fn (Product $product) => TextInput::make("products.{$product->id}")
                                ->label($product->name)
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->prefixAction(
                                    Action::make("decrement_product_{$product->id}")
                                        ->label('Decrement')
                                        ->icon(Heroicon::Minus)
                                        ->action(function (Get $get, Set $set) use ($product) {
                                            $current = $get->integer("products.{$product->id}");
                                            $set("products.{$product->id}", max(0, $current - 1));
                                        })
                                )
                                ->suffixAction(
                                    Action::make("increment_product_{$product->id}")
                                        ->label('Increment')
                                        ->icon(Heroicon::Plus)
                                        ->action(function (Get $get, Set $set) use ($product) {
                                            $current = $get->integer("products.{$product->id}");
                                            $set("products.{$product->id}", $current + 1);
                                        })
                                )
                            )->toArray()
                    ),
                Checkbox::make('input_given')
                    ->label('Promotional inputs given?')
                    ->live()
                    ->dehydrated(false),
                Section::make('Promotional Inputs')
                    ->visible(fn (Get $get) => $get->boolean('input_given'))
                    ->schema(
                        fn () => PromotionalInput::query()->orderBy('name')
                            ->get()
                            ->map(function (PromotionalInput $item) {
                                return TextInput::make("inputs.{$item->id}")
                                    ->label($item->name)
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->prefixAction(
                                        Action::make("decrement_input_{$item->id}")
                                            ->label('Decrement')
                                            ->icon(Heroicon::Minus)
                                            ->action(function (Get $get, Set $set) use ($item) {
                                                $current = $get->integer("inputs.{$item->id}");
                                                $set("inputs.{$item->id}", max(0, $current - 1));
                                            })
                                    )
                                    ->suffixAction(
                                        Action::make("increment_input_{$item->id}")
                                            ->label('Increment')
                                            ->icon(Heroicon::Plus)
                                            ->action(function (Get $get, Set $set) use ($item) {
                                                $current = $get->integer("inputs.{$item->id}");
                                                $set("inputs.{$item->id}", $current + 1);
                                            })
                                    );
                            })->toArray()
                    ),
                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(2)
                    ->maxLength(255),

            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visit Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('date')
                            ->label('Date')
                            ->date(),
                        TextEntry::make('doctor.name')
                            ->label('Doctor'),
                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Sample Products Distributed')
                    ->schema([
                        RepeatableEntry::make('sampleProducts')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product Name'),
                                TextEntry::make('quantity')
                                    ->label('Quantity'),
                            ])
                            ->columns(2)
                            ->placeholder('No sample products distributed.'),
                    ]),

                Section::make('Promotional Inputs Distributed')
                    ->schema([
                        RepeatableEntry::make('promotionalInputs')
                            ->label('')
                            ->schema([
                                TextEntry::make('promotionalInput.name')
                                    ->label('Promotional Input Name'),
                                TextEntry::make('quantity')
                                    ->label('Quantity'),
                            ])
                            ->columns(2)
                            ->placeholder('No promotional inputs distributed.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date(),
                TextColumn::make('doctor.name')
                    ->label('Doctor'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
                DeleteAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDCRS::route('/'),
        ];
    }
}
