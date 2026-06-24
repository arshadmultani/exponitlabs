<?php

namespace App\Filament\Resources\ProductPricings\Pages;

use App\Filament\Resources\ProductPricings\ProductPricingResource;
use Filament\Resources\Pages\ListRecords;

class ListProductPricings extends ListRecords
{
    protected static string $resource = ProductPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
