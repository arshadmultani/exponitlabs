<?php

namespace App\Filament\Resources\Microsites;

use App\Filament\Resources\Microsites\Pages\CreateMicrosite;
use App\Filament\Resources\Microsites\Pages\EditMicrosite;
use App\Filament\Resources\Microsites\Pages\ListMicrosites;
use App\Filament\Resources\Microsites\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Microsites\RelationManagers\ShowcasesRelationManager;
use App\Filament\Resources\Microsites\Schemas\MicrositeForm;
use App\Filament\Resources\Microsites\Tables\MicrositesTable;
use App\Models\Microsite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MicrositeResource extends Resource
{
    protected static ?string $model = Microsite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $modelLabel = 'Doctor Website';

    protected static ?string $recordTitleAttribute = 'url';

    public static function form(Schema $schema): Schema
    {
        return MicrositeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MicrositesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
            ShowcasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMicrosites::route('/'),
            'create' => CreateMicrosite::route('/create'),
            'edit' => EditMicrosite::route('/{record}/edit'),
        ];
    }
}
