# API Controllers Component Package

## Overview
Exposes RESTful sync API endpoints designed for local-first, offline-capable client applications (such as the Medical Representative mobile web app).

## Interface
- `App\Http\Controllers\Api\SyncController`:
  - `bootstrap(Request $request): JsonResponse`: Delivers initial dataset (active doctors, products, promotional inputs, headquarters, areas, and recent DCR history) to initialize client Dexie.js IndexedDB.
  - `sync(Request $request): JsonResponse`: Ingests queued offline mutations (new DCR logs, newly created doctors, reminder delivery logs) and reconciles state with server storage.

## Invariants
- Handles idempotent batch operations based on client-generated UUIDs.
- Validates field boundaries before persisting offline entries to server SQLite tables.
- Requires authenticated MR session or API token.

## Side Effects
- Bulk creation/updates across `dcrs`, `doctors`, `dcr_products`, and `dcr_promotional_inputs`.

## Verification
- Run: `php artisan test --compact --filter=OfflineMrFieldTest`
