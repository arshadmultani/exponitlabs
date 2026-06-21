<?php

namespace App\Filament\Resources\TherapeuticAreas;

use App\Filament\Resources\TherapeuticAreas\Pages\CreateTherapeuticArea;
use App\Filament\Resources\TherapeuticAreas\Pages\EditTherapeuticArea;
use App\Filament\Resources\TherapeuticAreas\Pages\ListTherapeuticAreas;
use App\Filament\Resources\TherapeuticAreas\Schemas\TherapeuticAreaForm;
use App\Filament\Resources\TherapeuticAreas\Tables\TherapeuticAreasTable;
use App\Models\TherapeuticArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TherapeuticAreaResource extends Resource
{
    protected static ?string $model = TherapeuticArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TherapeuticAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TherapeuticAreasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTherapeuticAreas::route('/'),
            'create' => CreateTherapeuticArea::route('/create'),
            'edit' => EditTherapeuticArea::route('/{record}/edit'),
        ];
    }
}
