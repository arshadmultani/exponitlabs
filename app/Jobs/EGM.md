# Jobs Component Package

## Overview
Asynchronous background and queued work units decoupled from synchronous HTTP request lifecycles.

## Interface
- `App\Jobs\RecordQrScanJob`: Asynchronously records a QR scan event and atomically increments scan statistics.
  - `__construct(public int $qrCodeId, public array $telemetry, public bool $isUnique = false)`
  - `handle(): void`: Locates the `QrCode` record, creates a `QrScan` row with client telemetry, and updates `total_scans`, `unique_scans`, and `last_scanned_at` within a database transaction.

## Invariants
- Operates on a database transaction (`DB::transaction`).
- Silently ignores requests if the referenced `qrCodeId` has been deleted.
- Updates scan metadata quietly (`updateQuietly`) without firing standard Eloquent model touched timestamps.

## Side Effects
- Database record creation in `qr_scans`.
- Counter increments and timestamp updates on `qr_codes`.

## Verification
- Run: `php artisan test --compact`
