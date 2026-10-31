# Product Pricings Resource Component Package

## Overview
Filament administration resource configuring wholesale, stockist, retail pricing schemes, and GST tax brackets across product formulations.

## Interface
- `App\Filament\Resources\ProductPricings\ProductPricingResource`: CRUD resource for `ProductPricing` model.
- **Pages**: `ListProductPricings`, `CreateProductPricing`, `EditProductPricing`.
- **Tables**: Columns for PTR, PTS, MRP, GST rate, and margin percentages.

## Invariants
- Each pricing record binds to a valid `Product`.
- PTS and PTR values must satisfy margin constraints relative to MRP.

## Verification
- Run: `php artisan test --compact`
