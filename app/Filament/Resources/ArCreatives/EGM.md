# AR Creatives Resource Component Package

## Overview
Filament administration resource managing Augmented Reality (AR) target images, compiled MindAR `.mind` tracking datasets, 3D models, video assets, and QR code associations.

## Interface
- `App\Filament\Resources\ArCreatives\ArCreativeResource`: Resource configuration for `ArCreative` model.
- **Pages**:
  - `ListArCreatives`: Paginated catalog with active state badges and preview action triggers.
  - `CreateArCreative`: Multistep form handling image upload, client-side `.mind` file generation, and video creative attachments.
  - `EditArCreative`: Asset updates, QR code re-generation, and visibility toggles.
- **Schemas & Tables**:
  - `ArCreativeForm`: Form definition supporting video and 3D file uploads (up to 50MB).
  - `ArCreativesTable`: Table schema with active status toggles, QR download action, and scan count metrics.

## Invariants
- Enforces unique slug generation from creative name.
- Only published creatives are visible via public viewer `/ar/{creative}`.

## Verification
- Run: `php artisan test --compact`
