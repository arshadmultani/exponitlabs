<?php

namespace App\Filament\Resources\ProductPricings;

use App\Filament\Resources\ProductPricings\Pages\ListProductPricings;
use App\Filament\Resources\ProductPricings\Tables\ProductPricingsTable;
use App\Models\ProductPricing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductPricingResource extends Resource
{
    protected static ?string $model = ProductPricing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyRupee;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'PTR / PTS';

    protected static ?string $modelLabel = 'pricing';

    protected static ?string $pluralModelLabel = 'PTR / PTS overview';

    /** Pricing is created/edited inside each product's "Pricing scenarios" tab. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ProductPricingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductPricings::route('/'),
        ];
    }
}
