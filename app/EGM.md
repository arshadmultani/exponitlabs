# App Subsystem Blueprint

## System Overview
The `app/` subsystem contains the core application code for Exponit Labs. It orchestrates the public marketing web application, the Filament v5 CMS administrative panel (`/console`), the local-first Medical Representative (MR) offline field module (`/mr`), the augmented-reality viewer (`/ar/{creative}`), and the dynamic QR code analytics pipeline.

## Subdirectories & System Boundaries
- [Ai](./Ai/EGM.md): Multimodal AI agents for handwriting transcription and structured extraction via `laravel/ai`.
- [Console](./Console/EGM.md): CLI maintenance commands, backup automation, and pharmaceutical audit analytics ingestion.
- [Filament](./Filament/EGM.md): Administrative CMS panel (`/console`) with 14 active resources, custom geolocation components, and dashboard widgets.
- [Http](./Http/EGM.md): Web and API controllers, public marketing routes, offline sync APIs, and doctor microsites.
- [Jobs](./Jobs/EGM.md): Asynchronous queued jobs for high-throughput QR code scan telemetry.
- [Models](./Models/EGM.md): Eloquent domain models, relationships, and SQLite database schema mapping.
- [Observers](./Observers/EGM.md): Model event observers keeping search and answer engine artifacts (`llms.txt`) synchronized.
- [Providers](./Providers/EGM.md): Service providers configuring application runtime boundaries and the Filament console panel.
- [Services](./Services/EGM.md): Business domain services for client device detection and edge geolocation capture.
- [Support](./Support/EGM.md): Shared utilities, Schema.org JSON-LD builders, QR generators, and pricing calculators.

## Global Invariants
- **Database Engine**: Uses SQLite storage with foreign key constraints enabled.
- **Testing Standard**: Every business logic change must be accompanied by Pest feature tests (`tests/Feature/`).
- **Formatting**: PHP files must strictly follow Laravel Pint conventions (`vendor/bin/pint --dirty --format agent`).
- **Offline Capability**: MR field modules (`/mr` and `app/Http/Controllers/Api/SyncController.php`) operate with client-generated UUIDs and idempotent syncing.

## Global Side Effects
- Mutates local SQLite database records (`database/database.sqlite`).
- Generates and writes static plain-text files (`public/llms.txt`, `public/llms-full.txt`).
- Dispatches queued background jobs and external webhooks (Discord alerts, Telegram backups).

## System Verification
- Run: `php artisan test --compact`
