# Services Component Package

## Overview
Domain business services encapsulating telemetry parsing, geolocation resolution, and device fingerprinting.

## Interface
- `App\Services\QrTelemetryService`:
  - `capture(Request $request): array`: Extracts client device type (`mobile`, `tablet`, `desktop`, `bot`), brand, model, OS, browser, IP address, and geographic location coordinates.

## Invariants
- Geolocation prioritizes Cloudflare edge request headers (`CF-IPCountry`, `CF-IPCity`) before falling back to `Stevebauman\Location`.
- Bot detection handled via Matomo `DeviceDetector`.

## Side Effects
- External IP geolocation resolution via `Stevebauman\Location\Facades\Location`.

## Verification
- Run: `php artisan test --compact`
