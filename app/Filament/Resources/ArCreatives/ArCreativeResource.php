<?php

namespace App\Filament\Resources\ArCreatives;

use BackedEnum;
use App\Models\ArCreative;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\ArCreatives\Pages\EditArCreative;
use App\Filament\Resources\ArCreatives\Pages\ListArCreatives;
use App\Filament\Resources\ArCreatives\Pages\CreateArCreative;
use App\Filament\Resources\ArCreatives\Schemas\ArCreativeForm;
use App\Filament\Resources\ArCreatives\Tables\ArCreativesTable;

class ArCreativeResource extends Resource
{
    protected static ?string $model = ArCreative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'AR Creatives';

    protected static ?string $modelLabel = 'AR creative';

    protected static ?string $pluralModelLabel = 'AR creatives';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ArCreativeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArCreativesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArCreatives::route('/'),
            'create' => CreateArCreative::route('/create'),
            'edit' => EditArCreative::route('/{record}/edit'),
        ];
    }
}
