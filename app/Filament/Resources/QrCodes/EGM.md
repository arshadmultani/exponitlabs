# QR Codes Resource Component Package

## Overview
Filament administration resource managing dynamic QR code generation, destination routing targets, scan quotas, visual branding, and real-time scan analytics.

## Interface
- `App\Filament\Resources\QrCodes\QrCodeResource`: Resource for `QrCode` model.
- **Pages**: `ListQrCodes`, `CreateQrCode`, `EditQrCode`, `ViewQrCode`.
- **Schemas & Tables**:
  - `QrCodeForm`: Code identifier, target URL, color customization, logo upload, expiration dates, and scan quotas.
  - `QrCodesTable`: Scan metrics (total/unique), status badges, quick test action, and PNG/SVG download buttons.
- **RelationManagers**:
  - `ScansRelationManager`: Granular audit log of scan events (timestamps, user agents, devices, locations).
- **Widgets**:
  - `QrCodeStatsWidget`: Aggregated time-series chart of daily scans and geographic distribution.

## Invariants
- Each QR code identifier is unique.
- Scan increments handled asynchronously without impacting redirect performance.

## Verification
- Run: `php artisan test --compact`
