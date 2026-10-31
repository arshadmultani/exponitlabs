# Headquarters Resource Component Package

## Overview
Filament administration resource managing top-level regional sales headquarters and territorial administrative zones.

## Interface
- `App\Filament\Resources\Headquarters\HeadquarterResource`: CRUD resource for `Headquarter` model.
- **Pages**: `ListHeadquarters`, `CreateHeadquarter`, `EditHeadquarter`.

## Invariants
- Headquarters manage clusters of geographic `Area` records.

## Verification
- Run: `php artisan test --compact`
