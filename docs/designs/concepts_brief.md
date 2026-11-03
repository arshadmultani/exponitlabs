# Doctor Call Cadence, Route Planning & Potential Tiers

* **Author:** EGM Ideation Swarm (Elephant + 6 Goldfish Reviewers)
* **Date:** 2026-11-02
* **Target:** Filament Administrative Console (`/console/doctor-cadence`)

---

## Overview

The Doctor Call Cadence & Route Planning system is an operational decision-support and territory management interface embedded within the Exponit Labs Filament v5 administrative console (`/console/doctor-cadence`). It eliminates undisciplined, mood-based daily visit scheduling across the company's 233 registered healthcare providers by giving sales leadership and field managers clear visibility into visit recency, call frequency adherence, doctor availability, and account potential.

The system introduces:
1. **Database-Backed Potential Tiers**: A dynamic `doctor_potentials` lookup table (`Core`, `Serious`, `Nascent`, `Unresponsive`) assigning target monthly detailing frequencies and visual priority to each account.
2. **Doctor Availability Scheduling**: Multi-select visiting days (Monday–Sunday) and visiting clinic shifts (Morning, Afternoon, Evening) configured on each doctor's master profile, with native support for "Everyday / All Shifts" open-door availability.
3. **DCR Cadence Ledger**: A high-performance, sortable administrative report calculating total detailing visits, all-time last visit dates, and days elapsed since the last call, equipped with territory, potential tier, visiting day, and shift filters to streamline daily beat planning.

---

## Analysis (Synthesized Swarm Findings)

The 6-lens Goldfish review swarm audited the feature concept against system architecture, codebase patterns, ergonomics, tech debt, and implementation safety:

### 1. Database Architecture & Potential Tier Storage (`egm-zoom-out` & `egm-codebase-details`)
- **Review Finding**: While a PHP 8.4 Backed Enum requires zero migration boilerplate, the user requirement dictates database storage so that business tiers, visit targets, and badge colors can be customized dynamically without code releases.
- **Synthesized Resolution**: Create a dedicated `doctor_potentials` table (`id`, `name`, `code`, `color`, `sort_order`, `target_visits_per_month`, `is_active`, `timestamps`).
  - Pre-seed with the 4 standard tiers:
    - **Core**: 4 visits/month (target interval: 7 days), color `success`.
    - **Serious**: 2 visits/month (target interval: 15 days), color `info`.
    - **Nascent**: 1 visit/month (target interval: 30 days, default for all 233 existing doctors), color `warning`.
    - **Unresponsive**: 0 visits/month (dormant/non-yielding accounts), color `danger`.
  - Add nullable foreign key `doctor_potential_id` on `doctors` with `nullOnDelete()`.

### 2. SQLite Date Calculations & The 140 Never-Visited Doctors Anomaly (`egm-design` & `egm-implementation-readiness`)
- **Review Finding**: 140 out of 233 doctors (60%) currently have zero recorded DCR visits in the database.
  - In SQLite, `MAX(date)` evaluates to `NULL` for unvisited doctors. Subtracting dates from `NULL` yields `NULL`.
  - Standard SQL `ASC` and `DESC` sorting behaves unpredictably with null values (putting never-visited doctors either at the bottom or scrambling them).
  - Furthermore, SQLite lacks MySQL's `DATEDIFF()` and evaluates `julianday('now')` in server UTC, inducing off-by-one errors in Indian Standard Time (IST, UTC+05:30).
- **Synthesized Resolution**:
  - Calculate elapsed days using SQLite Julian day arithmetic parameterized with application-local IST date: `CAST(julianday(?) - julianday(date(last_visit_date)) AS INTEGER)`.
  - Handle nulls safely in sorting using `COALESCE(days_since_last_visit, 999999)` so that uncontacted accounts reliably sort to the top when prioritizing overdue visits.
  - In the UI, render `Last Visit Date` as badge `Never Visited` (danger/neutral) and `Days Since Last Visit` as `Never` to prevent confusion with `0d ago` (Visited Today).

### 3. Decoupling Recency from Time-Horizon Filters (`egm-design` & `egm-zoom-out`)
- **Review Finding**: The initial concept proposed time-horizon tabs (`This Month`, `Current FY`, `All Time`).
  - If `Last Visit Date` is bounded by "This Month", any doctor not visited this month evaluates to `NULL` / "Never Visited", blinding managers to whether the doctor was seen 32 days ago or 400 days ago.
  - Route planning is an operational recency problem, not an accounting ledger.
- **Synthesized Resolution**:
  - **Decouple the Metrics**: `Last Visit Date` and `Days Since Last Visit` must ALWAYS be computed across **All Time** (`MAX(d_c_r_s.date)` with no lower date bound).
  - Only the visit count (`visits_in_period`) adapts to the selected horizon tab (`This Month` vs `Current FY` vs `All Time`).
  - In "This Month", compare `visits_this_month` against `doctor_potentials.target_visits_per_month` to display a progress ratio (e.g. `2 / 4 calls`).

### 4. Availability Scheduling & The "Everyday / Open Availability" Convention
- **Review Finding**: Storing `visiting_days` and `visiting_shifts` as separate JSON arrays requires a clean convention for doctors with open or full availability, and must avoid rendering sprawling badges that stretch table row heights.
- **Synthesized Resolution**:
  - **Database Schema**: Two nullable JSON columns on `doctors`:
    - `visiting_days` (JSON array: `['monday', 'tuesday', ...]`)
    - `visiting_shifts` (JSON array: `['morning', 'afternoon', 'evening']`)
  - **The Open-Door Convention ("Blank = Everyday / All Shifts")**:
    - If a doctor's schedule is `null` or empty, the system treats them as having **Open Availability (Available Everyday on All Shifts)**.
    - Zero data-entry overhead: Reps only check boxes when a doctor has *restricted* days (e.g. Wed/Thu only).
    - Form UI provides a 1-click `bulkToggleable()` "Select All" button above both checkbox lists.
  - **Table Layout**:
    - Unrestricted or full-schedule doctors display a compact emerald badge: `Everyday (All Shifts)`.
    - Restricted doctors display compact single-line tokens: `Mon · Wed · Fri (Morning)`.
  - **Filter Logic**:
    - Filtering by day (e.g. `Wednesday`) and shift (e.g. `Evening`) matches both explicitly tagged doctors AND open-availability doctors (`visiting_days IS NULL`).

### 5. High-Performance Query Architecture via Eloquent `fromSub()` (`egm-codebase-details` & `egm-implementation-readiness`)
- **Review Finding**: Computing `visits_count`, `last_visit_date`, and `days_since_last_visit` in PHP accessors or Livewire column closures causes an $O(N)$ N+1 query disaster and breaks database pagination and sorting.
- **Synthesized Resolution**:
  - Build `App\Services\DoctorCadenceQueryService` using Eloquent's `fromSub()` pattern established in `DoctorPortfolioQueryService`.
  - Correlated subqueries compute `actual_visits` and `last_visit_date` utilizing the existing composite index `idx_dcrs_doctor_date` on `d_c_r_s(doctor_id, date)`.
  - All derived fields (`days_since_last_visit`, `visit_deficit`, `coverage_percent`) project directly into the outer query as real SQL columns, enabling native sub-millisecond database sorting.

### 6. Offline Field App (`/mr`) Backward Compatibility (`egm-implementation-readiness`)
- **Review Finding**: In `SyncController::syncDoctors()`, medical reps create doctors offline. If `doctor_potential_id` is a non-nullable foreign key or unhandled in sync, offline batch uploads will crash with SQLite NOT NULL violations.
- **Synthesized Resolution**:
  - Declare `doctor_potential_id` as nullable in the migration.
  - In `Doctor::booted()` or `SyncController`, automatically assign the `Nascent` tier ID when a new doctor is created without an explicit tier.
  - Expose `doctor_potential_id`, `visiting_days`, and `visiting_shifts` in `/api/v1/sync/master-data` so mobile reps receive availability data offline.

---

## Goals vs. Non-Goals

### In-Scope Goals
1. **Dynamic Potential Tiers**: Migration and model for `doctor_potentials` pre-seeded with `Core`, `Serious`, `Nascent`, and `Unresponsive`.
2. **Doctor Master Integration**: Foreign key `doctor_potential_id` and JSON columns `visiting_days` and `visiting_shifts` added to `Doctor` model and `DoctorForm.php`.
3. **High-Performance Query Engine**: `DoctorCadenceQueryService` calculating total visits, all-time last visit dates, and days elapsed without N+1 overhead using `fromSub()` and indexed subqueries.
4. **Cadence & Route Planning Report**: Standalone Filament v5 page at `/console/doctor-cadence` under navigation group `Field Operations` with:
   - 3-zone balanced table layout with non-wrapping columns.
   - Status badges for potential tiers (`Core: success`, `Serious: info`, `Nascent: warning`, `Unresponsive: danger`).
   - Filters for Territory / Area, Potential Tier (default excludes `Unresponsive`), Day of Week, Shift, and Recency (>30d, >60d, Never Visited).
   - In-table Quick Actions: "Edit Schedule" slideover modal and "Log Visit" quick modal.
5. **Zero-Division & Null-Safety**: Graceful handling of never-visited accounts and date timezone boundaries.

### Out-of-Scope Non-Goals
1. **Automated Traveling Salesman / Turn-by-Turn GPS Routing**: No automated map routing or real-time traffic route optimization in v1. The report provides tabular territory filtering to inform manual rep planning.
2. **Strict Field Visit Blocking**: Visiting a doctor outside their registered schedule will NOT be blocked on `/mr`. Impromptu or rescheduled calls remain 100% permitted.
3. **Automated Tier Escalation/Downgrade Engine**: Tiers are manually assigned and maintained by sales managers in v1. Automated promotion based on secondary prescription revenue is deferred to v2.

---

## Key Decisions & Staging for Next Steps

| Decision Item | Decision Chosen | Technical Rationale |
| :--- | :--- | :--- |
| **Tier Storage** | Dedicated `doctor_potentials` table | Fulfills requirement for DB-driven tiers; allows dynamic adjustment of monthly target visit frequencies. |
| **Legacy Doctor Default** | Default all 233 doctors to `Nascent` | Prevents null cadence baselines; unclassified doctors operate under baseline monitoring until evaluated. |
| **Availability Model** | Two JSON columns (`visiting_days`, `visiting_shifts`) | Clean multi-select CheckboxList with `bulkToggleable()`; blank schedule defaults to Open Availability (Everyday). |
| **Recency Calculation** | All-time `MAX(d_c_r_s.date)` parameterized with local IST date | Prevents horizon tab filtering from artificially corrupting "last visit date" for accounts unvisited in the current month. |
| **Sorting on Unvisited** | `COALESCE(days_since_last_visit, 999999)` | Guarantees uncontacted accounts sort to the top when triaging overdue relationships in SQLite. |
| **Availability Filtering** | Native SQLite `json_each()` via `whereJsonContains` | Fast, typed JSON querying on SQLite 3.45.2 without false positive string matches. |

### Implementation Staging for `/egm-draft`
* **Phase 1**: Database Migrations & Seeds (`create_doctor_potentials_table`, alter `doctors`, seed default tiers).
* **Phase 2**: Model Contracts & Offline Sync Safety (`DoctorPotential` model, `Doctor` relationships/casts, `SyncController` defaults).
* **Phase 3**: Query Service (`DoctorCadenceQueryService` with `fromSub()` outer projections and Julian day math).
* **Phase 4**: Filament Admin UI (`DoctorForm` fields, `DoctorCadence` page at `/console/doctor-cadence`, row slideover actions).
* **Phase 5**: Automated Pest Verification (migration tests, cadence query tests, filter tests, and page rendering tests).
