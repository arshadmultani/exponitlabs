# DCR (Daily Call Reports) Resource Component Package

## Overview
Filament administration resource for inspecting and reviewing field Daily Call Reports submitted by Medical Representatives (MRs).

## Interface
- `App\Filament\Resources\DCRS\DCRResource`: Administrative review resource for `DCR` model.
- **Pages**: `ListDCRS`, `ViewDCR`.

## Invariants
- Visualizes visit date, MR representative, targeted doctor, detailed products, and sample inputs distributed.

## Verification
- Run: `php artisan test --compact`
