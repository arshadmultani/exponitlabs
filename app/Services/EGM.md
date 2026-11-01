# Services Component Package

## Overview
Domain business services encapsulating telemetry parsing, doctor commercial valuation queries, prescription revenue batch imports, geolocation resolution, and device fingerprinting.

## Interface
- `App\Services\DoctorPortfolioQueryService`:
  - `getBaseQuery(?Carbon $startDate, ?Carbon $endDate): Builder`: Correlated subqueries projecting `visits_count`, `samples_cost`, `promos_cost`, `total_revenue`, and derived sortable columns `total_investment` and `net_pnl` via `fromSub()`.
  - `resolveHorizonRange(string $horizon): array`: Computes date bounds for accounting horizons (`all_time`, `current_fy`, `last_3_months`, `last_month`).
  - `calculatePortfolioStats(?Carbon $startDate, ?Carbon $endDate): array`: Aggregates portfolio capital invested, attributed revenue, net P&L, and return multiple.
  - `calculateRoiPercentage(float $totalInvestment, float $netPnl): ?float`: Division-by-zero resilient ROI metric calculation.
  - `resolveTier(object $doctor): string`: Classifies physician commercial performance into `incubation`, `high_yield`, `balanced`, or `under_yielding`.
- `App\Services\DoctorRevenueImportService`:
  - `import(UploadedFile|string $file, ?Carbon $fallbackPeriod = null, ?int $userId = null): RevenueImportResult`: Ingests prescription revenue CSV audits, validates IDs in memory, performs atomic transactional persistence, and logs row errors.
- `App\Services\QrTelemetryService`:
  - `capture(Request $request): array`: Extracts client device type (`mobile`, `tablet`, `desktop`, `bot`), brand, model, OS, browser, IP address, and geographic location coordinates.

## Invariants
- Correlated aggregations executed directly within SQLite queries to avoid N+1 bottlenecks.
- Date bounds for financial years align with the Indian fiscal calendar (April 1 to March 31).
- CSV ingestion commits atomically within database transactions.
- Geolocation prioritizes Cloudflare edge request headers (`CF-IPCountry`, `CF-IPCity`) before falling back to `Stevebauman\Location`.
- Bot detection handled via Matomo `DeviceDetector`.

## Side Effects
- Database reads on `doctors`, `d_c_r_s`, `dcr_products`, `dcr_promotional_inputs`, and `doctor_revenues`.
- Batch records inserted into `doctor_revenues`.
- External IP geolocation resolution via `Stevebauman\Location\Facades\Location`.

## Verification
- Run: `php artisan test --compact`
