<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use App\Filament\Resources\DCRS\DCRResource;
use App\Models\DCR;
use App\Models\Product;
use App\Models\PromotionalInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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

                Select::make('products')
                    ->label('Sample Products')
                    ->placeholder('Select sample products')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn (Get $get) => $get->boolean('sample_given')),

                Checkbox::make('input_given')
                    ->label('Promotional inputs given?')
                    ->live()
                    ->dehydrated(false),

                Select::make('inputs')
                    ->label('Promotional Inputs')
                    ->placeholder('Select promotional inputs')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => PromotionalInput::query()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn (Get $get) => $get->boolean('input_given')),

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
            ->defaultGroup(null)
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
                            if (array_is_list($data['products'])) {
                                foreach ($data['products'] as $productId) {
                                    $record->sampleProducts()->create([
                                        'product_id' => $productId,
                                        'quantity' => 1,
                                    ]);
                                }
                            } else {
                                foreach ($data['products'] as $productId => $quantity) {
                                    if ((int) $quantity > 0) {
                                        $record->sampleProducts()->create([
                                            'product_id' => $productId,
                                            'quantity' => (int) $quantity,
                                        ]);
                                    }
                                }
                            }
                        }

                        if (! empty($data['inputs']) && is_array($data['inputs'])) {
                            if (array_is_list($data['inputs'])) {
                                foreach ($data['inputs'] as $inputId) {
                                    $record->promotionalInputs()->create([
                                        'promotional_input_id' => $inputId,
                                        'quantity' => 1,
                                    ]);
                                }
                            } else {
                                foreach ($data['inputs'] as $inputId => $quantity) {
                                    if ((int) $quantity > 0) {
                                        $record->promotionalInputs()->create([
                                            'promotional_input_id' => $inputId,
                                            'quantity' => (int) $quantity,
                                        ]);
                                    }
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
