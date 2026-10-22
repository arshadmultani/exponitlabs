<?php

namespace App\Filament\Resources\DCRS;

use App\Filament\Resources\DCRS\Pages\ManageDCRS;
use App\Models\DCR;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\PromotionalInput;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->relationship(
                        name: 'doctor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->with('area'),
                    )
                    ->getOptionLabelFromRecordUsing(function (Doctor $record): string {
                        $location = collect([$record->area?->name, $record->town])->filter(fn ($v) => filled($v))->implode(', ');

                        return '<div>'.e($record->name).'</div>'.($location ? '<div class="text-xs text-gray-500 dark:text-gray-400">'.e($location).'</div>' : '');
                    })
                    ->allowHtml()
                    ->searchable()
                    ->preload()
                    ->required(),

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['doctor.area']))
            ->defaultSort('date', 'desc')
            ->paginated([
                50, 100, 150, 200,
            ])
            ->defaultGroup(function ($livewire): ?Group {
                if (! ($livewire instanceof ManageDCRS)) {
                    return null;
                }

                return Group::make('date')
                    ->date()
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getDescriptionFromRecordUsing(function (DCR $record, ?Table $table = null): string {
                        $count = $table?->getLivewire()
                            ? $table->getLivewire()->getFilteredTableQuery()->whereDate('date', $record->date)->count()
                            : DCR::whereDate('date', $record->date)->count();

                        return "{$count} ".str('doctor call')->plural($count);
                    });
            })
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date(),
                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->description(fn (DCR $record): ?string => collect([$record->doctor?->area?->name, $record->doctor?->town])->filter(fn ($v) => filled($v))->implode(', ') ?: null),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()
                    ->label('')
                    ->mutateRecordDataUsing(function (array $data, DCR $record): array {
                        $productIds = $record->sampleProducts()->pluck('product_id')->toArray();
                        $inputIds = $record->promotionalInputs()->pluck('promotional_input_id')->toArray();

                        $data['sample_given'] = ! empty($productIds);
                        $data['products'] = $productIds;

                        $data['input_given'] = ! empty($inputIds);
                        $data['inputs'] = $inputIds;

                        return $data;
                    })
                    ->after(function (DCR $record, array $data): void {
                        $record->sampleProducts()->delete();
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

                        $record->promotionalInputs()->delete();
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
