<?php

namespace App\Filament\Resources\ProductPricings\Tables;

use App\Filament\Resources\Products\ProductResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductPricingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('product_id')
            // Each row links to its product (where pricing is managed).
            ->recordUrl(fn ($record) => $record->product
                ? ProductResource::getUrl('edit', ['record' => $record->product])
                : null)
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('label')
                    ->label('Scenario')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('scheme')
                    ->label('Scheme')
                    ->state(fn ($record) => $record->scheme_paid_units.'+'.$record->scheme_free_units),

                TextColumn::make('mrp_snapshot')->label('MRP')->money('INR')->sortable(),
                TextColumn::make('ptr')->label('PTR')->money('INR')->sortable(),
                TextColumn::make('pts')->label('PTS')->money('INR')->sortable(),
                TextColumn::make('effective_pts')->label('Eff. PTS')->money('INR')->sortable(),

                TextColumn::make('profit_per_unit')
                    ->label('Profit/unit')->money('INR')->placeholder('—')->toggleable(),
                TextColumn::make('profit_margin_percent')
                    ->label('Margin %')->suffix('%')->placeholder('—')->toggleable(),

                TextColumn::make('gst_percent')
                    ->label('GST')->suffix('%')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('supply_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'inter_state' ? 'Inter-state' : 'Intra-state')
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
