<?php

namespace App\Filament\Resources\DoctorPotentials;

use App\Filament\Resources\DoctorPotentials\Pages\ManageDoctorPotentials;
use App\Models\DoctorPotential;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DoctorPotentialResource extends Resource
{
    protected static ?string $model = DoctorPotential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Field Operations';

    protected static ?string $navigationLabel = 'Commercial Tiers';

    protected static ?string $modelLabel = 'Commercial Potential Tier';

    protected static ?string $pluralModelLabel = 'Commercial Potential Tiers';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tier Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Core, Serious, Nascent'),

                TextInput::make('code')
                    ->label('Identifier Code')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., core, serious, nascent, unresponsive'),

                TextInput::make('target_visits_per_month')
                    ->label('Target Calls / Month')
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->required()
                    ->helperText('Number of monthly detailing calls reps must conduct with physicians in this tier.'),

                Select::make('color')
                    ->label('Badge Color')
                    ->options([
                        'success' => 'Green (Success)',
                        'info' => 'Blue (Info)',
                        'warning' => 'Amber (Warning)',
                        'danger' => 'Red (Danger)',
                        'gray' => 'Gray',
                    ])
                    ->default('info')
                    ->required(),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Tier')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('target_visits_per_month')
                    ->label('Target Frequency')
                    ->state(fn (DoctorPotential $record): string => "{$record->target_visits_per_month} calls / month")
                    ->badge()
                    ->color(fn (DoctorPotential $record): string => $record->color ?? 'gray')
                    ->sortable(),

                TextColumn::make('doctors_count')
                    ->counts('doctors')
                    ->label('Doctors Assigned')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageDoctorPotentials::route('/'),
        ];
    }
}
