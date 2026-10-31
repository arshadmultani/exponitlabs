# Areas Resource Component Package

## Overview
Filament administration resource managing geographic sales territories and doctor area groupings under headquarters.

## Interface
- `App\Filament\Resources\Areas\AreaResource`: CRUD resource for `Area` model.
- **Pages**: `ListAreas`, `CreateArea`, `EditArea`.
- **Schemas & Tables**:
  - `AreaForm`: Territory naming, headquarters selection, and postal code mapping.
  - `AreasTable`: Paginated table with headquarters filters and doctor count aggregations.

## Invariants
- Each Area belongs to a parent Headquarter.

## Verification
- Run: `php artisan test --compact`
