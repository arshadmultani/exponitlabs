# Therapeutic Areas Resource Component Package

## Overview
Filament administration resource managing clinical therapeutic categories (Pain Management, Gastro Care, Antibiotics, Allergy) grouping products on the public website and field catalog.

## Interface
- `App\Filament\Resources\TherapeuticAreas\TherapeuticAreaResource`: Full CRUD resource for `TherapeuticArea` model.
- **Pages**: `ListTherapeuticAreas`, `CreateTherapeuticArea`, `EditTherapeuticArea`.
- **Schemas & Tables**:
  - `TherapeuticAreaForm`: Specialty title, slug, description, SVG icon, and display sort order.
  - `TherapeuticAreasTable`: Category list with product count badges and visibility toggles.

## Invariants
- Enforces unique slug; active status controls public visibility on `/` and `/products`.
- Modifying or deleting therapeutic areas triggers `TherapeuticAreaObserver` to synchronize `public/llms.txt`.

## Verification
- Run: `php artisan test --compact`
