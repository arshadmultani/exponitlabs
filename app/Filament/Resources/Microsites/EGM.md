# Microsites Resource Component Package

## Overview
Filament administration resource configuring standalone, branded per-doctor landing pages accessible via `/dr/{slug}`.

## Interface
- `App\Filament\Resources\Microsites\MicrositeResource`: CMS resource for `Microsite` model.
- **Pages**: `ListMicrosites`, `CreateMicrosite`, `EditMicrosite`.
- **Schemas & Tables**:
  - `MicrositeForm`: Custom banner, doctor bio, booking links, and theme configuration.
  - `MicrositesTable`: Active status flags, public link actions, and view metrics.
- **RelationManagers**:
  - `ShowcasesRelationManager`: Embedded clinic showcases and facility galleries.
  - `ReviewsRelationManager`: Patient testimonials and review ratings.

## Invariants
- Enforces unique slug per microsite; public routes resolve strictly via active slugs.

## Verification
- Run: `php artisan test --compact`
