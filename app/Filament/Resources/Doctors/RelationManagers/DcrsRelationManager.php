<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use App\Filament\Resources\DCRS\DCRResource;
use App\Models\DCR;
use App\Models\Product;
use App\Models\PromotionalInput;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DcrsRelationManager extends RelationManager
{
    protected static string $relationship = 'dcrs';

    protected static ?string $relatedResource = DCRResource::class;

    protected static ?string $title = 'Daily Call Reports (DCRs)';

    protected static ?string $modelLabel = 'DCR';

    protected static ?string $pluralModelLabel = 'DCRs';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Visit Date')
                    ->required()
                    ->default(now()->toDateString()),

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
                            ->map(fn (PromotionalInput $item) => TextInput::make("inputs.{$item->id}")
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
                                )
                            )->toArray()
                    ),

                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(2)
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('No DCRs recorded yet')
            ->emptyStateDescription('No daily call reports have been filed for this doctor till date.')
            ->modifyQueryUsing(fn ($query) => $query->with(['sampleProducts.product', 'promotionalInputs.promotionalInput']))
            ->columns([
                TextColumn::make('date')
                    ->label('Visit Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(50)
                    ->tooltip(fn (DCR $record): ?string => $record->remarks)
                    ->placeholder('No remarks')
                    ->searchable(),

                TextColumn::make('sample_products')
                    ->label('Samples Distributed')
                    ->state(function (DCR $record): array {
                        return $record->sampleProducts->map(function ($item) {
                            $name = $item->product?->name ?? 'Product';

                            return "{$name} (x{$item->quantity})";
                        })->all();
                    })
                    ->badge()
                    ->color('success')
                    ->placeholder('None'),

                TextColumn::make('promotional_inputs')
                    ->label('Promotional Inputs')
                    ->state(function (DCR $record): array {
                        return $record->promotionalInputs->map(function ($item) {
                            $name = $item->promotionalInput?->name ?? 'Input';

                            return "{$name} (x{$item->quantity})";
                        })->all();
                    })
                    ->badge()
                    ->color('info')
                    ->placeholder('None'),

                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Log DCR')
                    ->icon(Heroicon::OutlinedPlus)
                    ->after(function (DCR $record, array $data): void {
                        if (! empty($data['products']) && is_array($data['products'])) {
                            foreach ($data['products'] as $productId => $quantity) {
                                if ((int) $quantity > 0) {
                                    $record->sampleProducts()->create([
                                        'product_id' => $productId,
                                        'quantity' => (int) $quantity,
                                    ]);
                                }
                            }
                        }

                        if (! empty($data['inputs']) && is_array($data['inputs'])) {
                            foreach ($data['inputs'] as $inputId => $quantity) {
                                if ((int) $quantity > 0) {
                                    $record->promotionalInputs()->create([
                                        'promotional_input_id' => $inputId,
                                        'quantity' => (int) $quantity,
                                    ]);
                                }
                            }
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->schema([
                        Section::make('Visit Details')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('date')
                                    ->label('Visit Date')
                                    ->date('M d, Y'),
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
                    ]),
                DeleteAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
