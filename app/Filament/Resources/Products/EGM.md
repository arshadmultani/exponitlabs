# Products Resource Component Package

## Overview
Filament administration resource managing the pharmaceutical product catalog, composition details, packaging, therapeutic area classifications, indications, and visual product media.

## Interface
- `App\Filament\Resources\Products\ProductResource`: Full CRUD catalog resource for `Product` model.
- **Pages**: `ListProducts`, `CreateProduct`, `EditProduct`.
- **Schemas & Tables**:
  - `ProductForm`: Brand name, slug, composition, packaging specs, active ingredients, indications, therapeutic area picker, and product pack shots.
  - `ProductsTable`: Category filters, search by active ingredient or brand name, active status toggle.
- **RelationManagers**:
  - `ProductPricingsRelationManager`: Inline management of price points, stockist margins, and GST rates.

## Invariants
- Product slugs are unique across the catalog.
- Modifying or saving products triggers `ProductObserver` to rebuild `public/llms.txt`.

## Verification
- Run: `php artisan test --compact`
