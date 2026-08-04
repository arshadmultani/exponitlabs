# Local-First Field App (DCR & Doctor Management) Implementation Plan

## Executive Architecture Summary

This plan outlines the architecture for building a **Local-First, Offline-Capable Field Module** for Medical Representatives (MRs), covering **DCR Entry**, **Doctor Directory & Search**, **Doctor Detail Views**, and **Offline Doctor Creation**.

### Will this be outside the Filament Panel?
**Yes.** 
* **Admin & Management Panel (Filament):** Stays as-is for managers, admins, approvals, master data maintenance, and reporting on stable connections.
* **MR Field App (Custom Blade + Alpine.js):** Lives outside Filament under a `/mr` route group (`/mr/dcr`, `/mr/doctors`, `/mr/doctors/{uuid}`). This provides a fast, mobile-first interface optimized for zero server roundtrips, zero layout bloat, and full offline execution.

---

## 1. Required Dependencies & Tech Stack

### Existing in Project:
- **PHP 8.4** & **Laravel 13**
- **Alpine.js v3** (UI State & Reactive Data Binding)
- **Tailwind CSS v4** (Mobile-first responsive styling)

### New Dependencies to Install:
1. **Dexie.js** (`npm install dexie`): Minimal, high-performance wrapper around IndexedDB for browser database management.
2. **Native Web APIs**: `crypto.randomUUID()` (Client-side UUID generation), `Service Worker API` (App Shell caching), `navigator.onLine` (Network detection).

---

## 2. System Architecture & Data Flow

```
+---------------------------------------------------------------------------------------+
|                                   MR CLIENT DEVICE                                    |
|                                                                                       |
|  [PWA Service Worker] -> Serves cached Blade App Shells & Assets (100% Offline)       |
|                                                                                       |
|  [Blade Views under /mr]                                                              |
|   ├── /mr/dcr             (Instant DCR Entry)                                         |
|   ├── /mr/doctors         (Instant Doctor Search & List)                              |
|   ├── /mr/doctors/create  (Offline Doctor Addition)                                  |
|   └── /mr/doctors/{uuid}  (Offline Doctor Profile & Visit History)                    |
|                                                                                       |
|  [Alpine.js State] <=== (Instant <1ms Reads/Writes) ===> [Dexie.js IndexedDB]        |
|                                                              ├── doctors              |
|                                                              ├── products             |
|                                                              ├── promotional_inputs   |
|                                                              ├── doctor_outbox        |
|                                                              └── dcr_outbox           |
|                                                                                       |
|                          [Background Sync Engine (JS)]                                |
+---------------------------------------------------------------------------------------+
                                           ||
                                (HTTP / JSON API when online)
                                           ||
                                           \/
+---------------------------------------------------------------------------------------+
|                                    LARAVEL SERVER                                     |
|                                                                                       |
|  GET  /api/v1/sync/master-data  -> Returns Assigned Doctors, Products & Inputs      |
|  POST /api/v1/sync/doctors-batch -> Validates & inserts offline-created Doctors      |
|  POST /api/v1/sync/dcr-batch     -> Accepts pending DCR queue & writes DB transaction |
+---------------------------------------------------------------------------------------+
```

---

## 3. Scope of Offline Modules

### Module A: Offline Doctor Management (`/mr/doctors`)
1. **Doctor List & Search (`/mr/doctors`):**
   - Instant search by doctor name, specialty, clinic address, or phone number.
   - Filter by specialty or territory.
   - Works 100% offline from local Dexie `doctors` store.
2. **Doctor Profile & History (`/mr/doctors/{uuid}`):**
   - Displays doctor details (qualification, phone, email, clinic address, preferred visit time).
   - Displays past local & synced visit history (last DCR date, products sampled, inputs gifted).
   - Works 100% offline.
3. **Offline Doctor Creation (`/mr/doctors/create` or inline modal):**
   - Allows MR to add a new doctor directly in the field.
   - Generates client-side `uuid: crypto.randomUUID()`.
   - Immediately adds doctor to local `doctors` store and `doctor_outbox` with `sync_status: 'pending'`.
   - **Crucial:** Newly added doctor is *immediately available* across both the Doctor Directory AND the DCR form selector without waiting for server sync.

### Module B: Offline DCR Entry (`/mr/dcr`)
1. **Instant Form Entry:** Select doctor (including newly added offline doctors), products sampled, promotional inputs, and remarks.
2. **Local Queue:** Saves to `dcr_outbox` with `client_uuid` and `status: 'pending'`.

---

## 4. Step-by-Step Implementation Blueprint

### Phase 1: Database & Migration Preparation

1. **UUID & Offline Tracking on Server Tables:**
   - Ensure `doctors` table has a `uuid` column (CHAR 36 / UUID, indexed, unique).
   - Ensure `dcrs` table has a `uuid` column (CHAR 36 / UUID, indexed).
   - Add `sync_status` or timestamps to track when doctor master records were last updated.
2. **Sync Indexing:**
   - Index `updated_at` on `doctors`, `products`, and `promotional_inputs` for delta sync querying.

---

### Phase 2: Server Sync API Endpoints

#### 1. Master Data Download Endpoint (`GET /api/v1/sync/master-data`)
* **Query Param:** `?since=2026-08-04T00:00:00Z`
* **Returns:** Assigned doctors (including server-side assigned IDs & UUIDs), products, promotional inputs, and recent DCR history snippet per doctor.

#### 2. Doctor Batch Sync Endpoint (`POST /api/v1/sync/doctors-batch`)
* **Payload:** Array of new doctors created offline by MR.
```json
{
  "doctors": [
    {
      "uuid": "doc-uuid-999-abc",
      "name": "Dr. Rajesh Verma",
      "specialty": "Pediatrics",
      "phone": "+919876543210",
      "clinic_address": "Civic Center, Block B",
      "city": "Mumbai",
      "created_at_client": "2026-08-04T17:30:00Z"
    }
  ]
}
```
* **Server Logic:**
  - Validates phone/name uniqueness.
  - Inserts doctor record and assigns server `id`.
  - Returns mapping: `[{ "uuid": "doc-uuid-999-abc", "server_id": 450, "status": "synced" }]`.

#### 3. DCR Batch Sync Endpoint (`POST /api/v1/sync/dcr-batch`)
* **Server Logic:**
  - Accepts pending DCRs.
  - Resolves `doctor_uuid` to server `doctor_id` if the doctor was created offline.
  - Inserts DCRs in a single database transaction.

---

### Phase 3: Client Storage (Dexie.js Schema)

Updated `resources/js/offline-db.js`:

```javascript
import Dexie from 'dexie';

export const db = new Dexie('ExponitMRDatabase');

db.version(2).stores({
  doctors: 'uuid, id, name, specialty, phone, city, sync_status',
  products: 'id, name, category',
  promotional_inputs: 'id, name',
  doctor_outbox: 'uuid, name, sync_status, created_at_client',
  dcr_outbox: 'client_uuid, doctor_uuid, date, status, created_at_client',
  visit_history: 'uuid, doctor_uuid, date',
  sync_meta: 'key, value'
});
```

---

### Phase 4: Routes, Blade Layout & Views

1. **Routes (`routes/web.php`):**
   ```php
   Route::middleware(['auth'])->prefix('mr')->name('mr.')->group(function () {
       Route::get('/dcr', [MRDcrController::class, 'index'])->name('dcr');
       Route::get('/doctors', [MRDoctorController::class, 'index'])->name('doctors.index');
       Route::get('/doctors/create', [MRDoctorController::class, 'create'])->name('doctors.create');
       Route::get('/doctors/{uuid}', [MRDoctorController::class, 'show'])->name('doctors.show');
   });
   ```

2. **Layout (`resources/views/layouts/mr.blade.php`):**
   - Shared mobile layout with header status bar:
     - `Online (Synced)` / `Offline (3 pending syncs)`
   - Bottom Tab Navigation: `[DCR Entry]` | `[Doctor Directory]` | `[Sync Status]`

3. **Blade Views:**
   - `resources/views/mr/dcr.blade.php`
   - `resources/views/mr/doctors/index.blade.php` (Search, list, filter)
   - `resources/views/mr/doctors/create.blade.php` (New Doctor Form)
   - `resources/views/mr/doctors/show.blade.php` (Doctor profile & visit log)

---

### Phase 5: Alpine.js Components & Sync Engine

1. **Doctor List Component (`x-data="doctorListApp()"`):**
   - Loads doctors from Dexie `doctors` table instantly.
   - Reactive text search across `name`, `specialty`, `phone`, and `clinic_address`.
   - Renders badge indicating `Synced` vs `Local (Pending Sync)`.

2. **Doctor View Component (`x-data="doctorDetailApp('uuid')"`):**
   - Retrieves doctor record and past DCR visit logs from Dexie by `uuid`.

3. **Doctor Create Component (`x-data="doctorCreateApp()"`):**
   - On submit, creates local record in Dexie:
     ```javascript
     const newDoc = {
       uuid: crypto.randomUUID(),
       name: this.form.name,
       specialty: this.form.specialty,
       phone: this.form.phone,
       clinic_address: this.form.clinic_address,
       sync_status: 'pending',
       created_at_client: new Date().toISOString()
     };
     await db.doctors.add(newDoc);
     await db.doctor_outbox.add(newDoc);
     ```
   - Redirects to `/mr/doctors` or returns to `/mr/dcr` with new doctor auto-selected!

4. **Unified Sync Engine (`resources/js/sync-engine.js`):**
   - Runs background sync sequence when online:
     1. Push pending doctors from `doctor_outbox` to `/api/v1/sync/doctors-batch`.
     2. Update local `doctors` table with server response IDs & mark `sync_status: 'synced'`.
     3. Push pending DCRs from `dcr_outbox` to `/api/v1/sync/dcr-batch`.
     4. Pull latest master data updates from `/api/v1/sync/master-data`.

---

### Phase 6: PWA Service Worker & App Shell Caching

Update `public/sw.js`:
- Cache routes `/mr/dcr`, `/mr/doctors`, `/mr/doctors/create`.
- Cache assets (JS bundles, CSS, fonts).
- Enable 100% offline load of all `/mr/*` pages.

---

## 5. Execution Checklist (When ready to build)

When ready to implement, follow this exact sequence:

- [ ] **Step 1:** Create `routes/web.php` routes for `/mr/dcr` and `/mr/doctors/*`.
- [ ] **Step 2:** Create Blade layout `resources/views/layouts/mr.blade.php` and view files.
- [ ] **Step 3:** Setup Dexie DB (`resources/js/offline-db.js`) with `doctors` and `doctor_outbox` tables.
- [ ] **Step 4:** Build Laravel API controllers: `DoctorSyncController`, `MasterDataSyncController`, `DcrSyncController`.
- [ ] **Step 5:** Build Alpine components: `doctorListApp()`, `doctorDetailApp()`, `doctorCreateApp()`, and `dcrApp()`.
- [ ] **Step 6:** Build Unified Sync Engine (`resources/js/sync-engine.js`).
- [ ] **Step 7:** Create `public/sw.js` for PWA offline app shell caching across all `/mr/*` views.
- [ ] **Step 8:** Comprehensive offline testing (Network: Offline in Chrome DevTools).
