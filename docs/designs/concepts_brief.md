# Centralized Indian Festival & Healthcare Observance Calendar Engine

* **Author:** EGM Ideation Swarm (Elephant + 6 Goldfish Reviewers)
* **Date:** 2026-11-03
* **Target:** Filament Administrative Console (`/console/festivals`) & Offline MR Field App (`/elos`)

---

## Overview

The Centralized Indian Festival & Healthcare Observance Calendar Engine equips Exponit Labs with an authoritative, database-backed calendar system to manage major Indian cultural festivals, national holidays, and healthcare observances across 2025–2030. 

Rather than recording sensitive personal doctor attributes (such as religion or community), the system approaches relationship-building through an objective, calendar-driven greeting engine. Administrators maintain and adjust festival dates centrally within a responsive, month-by-month calendar grid in the Filament admin console (`/console/festivals`)—enabling real-time adjustments for astronomical moon sightings (e.g., Eid-ul-Fitr, Eid-ul-Adha) and regional calendar variations.

In the offline-first field app used by Medical Representatives (`/elos`), active greeting windows automatically highlight upcoming observances without requiring continuous network connectivity, seamlessly connecting into the existing celebration card canvas and festive text generation engine.

---

## Analysis (Synthesized Multi-Lens Review)

The 6-lens Goldfish review swarm audited the feature hypothesis across system architecture, codebase patterns, ergonomics, tech debt, and implementation safety:

### 1. The Sync Invalidation & Sliding Window Paradox (`egm-design`, `egm-implementation-readiness`)
- **The Risk:** The existing ELOS sync architecture (`SyncController::masterData`) relies on incremental delta updates using `?since=<last_synced_at>` filtering on `updated_at >= $since`. If the server attempts to serve a dynamic "rolling 60-day window" of festivals, pre-seeded 2025–2030 records will have static creation timestamps from initial seeding. When time simply advances, upcoming festivals entering the 60-day horizon will have older timestamps and be omitted by `?since`. Furthermore, when dates shift or expire, incremental `bulkPut()` leaves obsolete ghost records in IndexedDB.
- **Resolution:** Decouple festival synchronization from the `?since` timestamp filter. Multi-year calendar data is tiny (~40 observances/year × 6 years ≈ 240 records ≈ 16 KB uncompressed). The `masterData` endpoint will sync the full active dataset (or current year + next year), and the client sync engine will execute an atomic table clear-and-replace in Dexie.js (`db.upcoming_festivals.clear(); db.upcoming_festivals.bulkPut(payload);`).

### 2. Seeder Mutability Safeguard vs. Moon-Sighting Overrides (`egm-structural`, `egm-codebase-details`, `egm-comprehension`)
- **The Risk:** Indian Gazetted and Islamic holidays frequently shift by 1–2 days based on local Hilal moon sightings or state notifications. If an administrator adjusts a date in `/console/festivals`, routine zero-downtime CI/CD deployments running `php artisan db:seed` with naive `updateOrCreate` would clobber production administrative corrections with static JSON dates.
- **Resolution:** Add an `is_manually_adjusted` boolean flag and an `adjusted_by_user_id` foreign key on the `festival_dates` table. The seeder and dataset import command will check this flag and skip existing records where `is_manually_adjusted === true` unless an explicit `--force` flag is passed.

### 3. Immediate Reuse of Existing Celebration Card & Copy Engine (`egm-zoom-out`)
- **The Opportunity:** The initial concept deferred card generation as a future phase. However, Exponit Labs already contains a production-ready, client-side 1080x1080 Canvas card rendering engine in `resources/js/reminder-cards/celebration.js`, paired with occasion copy templates (`festive_greetings`, `independence_day`, `birthday_wishes`) in `resources/js/reminder-cards/text-engine.js`.
- **Resolution:** Zero new graphics or canvas engines need to be written. When an active greeting window opens, the field app displays an "Active Observance" banner. Tapping it opens the existing Call Reminder Studio modal (`mr.components.call-reminder-modal`) with the `celebration` card template pre-selected and festive greeting copy pre-populated.

### 4. Blanket Greetings vs. Clinical Specialty Observance Guardrail (`egm-design`, `egm-comprehension`)
- **The Risk:** Applying blanket greetings universally to all doctors works well for cultural and national celebrations (Diwali, Eid, Christmas, Republic Day, National Doctor's Day on July 1). However, treating clinical specialty days (e.g., World Glaucoma Day, World Spine Day, World Dialysis Day) as universal broadcasts would prompt MRs to send irrelevant clinical greetings to unrelated specialists (e.g., wishing an Orthopedic Surgeon on World Glaucoma Day), degrading field credibility.
- **Resolution:** Classify observances into two explicit categories:
  1. `cultural_and_national`: Universal greeting windows displayed across all doctor profiles.
  2. `clinical_observance`: Targeted observances with an optional `target_specialties` JSON array (e.g., World Heart Day → `['Cardiology', 'Internal Medicine']`). If populated, the banner only surfaces on matching physician profiles.

### 5. Native Livewire v4 Calendar Grid Architecture (`egm-zoom-out`, `egm-codebase-details`)
- **The Risk:** Embedding third-party calendar npm/composer packages (e.g., FullCalendar wrappers) inside Filament v5 introduces heavy bundle bloat, styling conflicts with Tailwind v4, and upstream upgrade fragility.
- **Resolution:** Implement `/console/festivals` as a custom Filament Page (`App\Filament\Pages\FestivalsCalendar`) leveraging a native Livewire 4 month-grid Blade template styled with standard 7-column Tailwind CSS grid (`grid-cols-7`). Clicking any festival opens a standard Filament slide-over modal action (`Filament\Actions\Action::make('editDate')`) to modify the date and record the manual override flag.

### 6. Timezone Invariants & Midnight Offsets (`egm-structural`, `egm-implementation-readiness`)
- **The Risk:** Doctor visits and festival greetings are strictly local to India. Evaluating greeting windows across device-local JavaScript `new Date()` and server UTC timestamps risks 5.5-hour off-by-one errors across midnight thresholds.
- **Resolution:** Enforce Indian Standard Time (`Asia/Kolkata`, UTC+05:30) throughout the domain model. All backend queries anchor to `Carbon::now('Asia/Kolkata')->startOfDay()`, and client-side calculations evaluate dates against clean `YYYY-MM-DD` strings.

---

## Goals vs. Non-Goals

### In-Scope Goals
1. **Authoritative Dataset Ingestion**: Pre-bundle verified 2025–2030 dates in `database/data/indian_festivals.json` derived from GoI DoPT Gazetted orders, IMD Rashtriya Panchang, and IMA National Doctor's Day.
2. **Database Schema & Models**: Create `festivals` and `festival_dates` tables with composite indexes on `['date', 'festival_id']` and `is_manually_adjusted` protection.
3. **Filament v5 Monthly Calendar Grid**: Build `/console/festivals` with month navigation stepper, category color badges, and slide-over date editing.
4. **Active Greeting Window Engine**: Formulate date arithmetic: a festival date $D$ is active on target date $T$ if $(D - \text{days\_before}) \le T \le (D + \text{days\_after})$.
5. **Offline Master Data Sync**: Update `SyncController::masterData` to include pre-computed upcoming festival windows and upgrade Dexie.js to schema version 6 (`upcoming_festivals` store).
6. **Field App Awareness**: Surface contextual festival banners in `/elos/dcr` and doctor profiles during active windows, pre-selecting the existing `celebration` card template.

### Out-of-Scope Non-Goals
1. **Doctor Religious or Community Profiling**: Strictly excluded. No religion or community fields will be added to doctor records.
2. **Custom Visual Card / Canvas Generation**: Strictly deferred. Will directly reuse the existing client-side `celebration.js` canvas engine.
3. **Client-Side Video Generation**: Explicitly excluded from Phase 1.
4. **Automated WhatsApp Broadcasting / Push Bots**: Strictly rep-initiated. Greetings are sent via rep-reviewed click-to-chat (`wa.me`) to protect against WhatsApp bulk spam detection.

---

## Key Decisions & Architecture Staging

| Decision Item | Decision Chosen | Technical Rationale |
| :--- | :--- | :--- |
| **Data Authority** | `database/data/indian_festivals.json` + `festival_dates` table | Multi-year pre-bundling ensures zero runtime external API dependency, while SQLite allows central date overrides. |
| **Seeder Safety** | Skip existing records where `is_manually_adjusted = true` | Prevents future deployments from overwriting real-world moon sighting date adjustments. |
| **Calendar Grid UX** | Native Livewire v4 Blade component (Tailwind `grid-cols-7`) | Zero third-party package dependencies; full design cohesion with Filament v5 and Tailwind v4. |
| **Sync Strategy** | Full-table atomic replacement for active years in Dexie | Eliminates the sliding window vs. `?since` timestamp sync trap. |
| **Specialty Scope** | Universal for Cultural/National; Filtered for Clinical Observances | Prevents irrelevant clinical greeting spam to specialized physicians. |
| **Card Pipeline** | Direct integration with existing `celebration.js` canvas template | 100% codebase reuse; immediate production utility without building new graphics tooling. |

---

### Implementation Staging for `/egm-draft`

* **Phase 1: Database Schema & Dataset Ingestion**
  - Create migrations for `festivals` and `festival_dates`.
  - Compile `database/data/indian_festivals.json` (2025–2030) with official DoPT, IMD, and IMA dates.
  - Create `FestivalSeeder` with `is_manually_adjusted` override protection.
* **Phase 2: Festival Query Service & Timezone Invariant**
  - Implement `FestivalGreetingService` calculating active and upcoming windows in `Asia/Kolkata`.
  - Write Pest unit tests covering before/after window boundary arithmetic.
* **Phase 3: Filament Admin Calendar Grid (`/console/festivals`)**
  - Create `App\Filament\Pages\FestivalsCalendar` with month-by-month Livewire grid view.
  - Implement slide-over `EditDateAction` that updates dates and sets `is_manually_adjusted = true`.
* **Phase 4: Sync Engine & Dexie.js Schema Upgrade (v6)**
  - Add `festivals` payload to `SyncController::masterData` with pre-computed window boundaries.
  - Bump Dexie schema to `v6` in `resources/js/offline-db.js` and add atomic replace in `resources/js/sync-engine.js`.
* **Phase 5: Field App Greeting Banner & Studio Integration**
  - Add active festival banner in `/elos/dcr` and doctor profiles.
  - Pre-fill Call Reminder Studio with `celebration` card template and festive greeting text on click.
* **Phase 6: Automated Verification & Documentation**
  - Feature test suite in Pest 4 covering calendar rendering, date overrides, sync payload, and offline Dexie upgrade.
  - Register documentation in `docs/designs/SITEMAP.md`.
