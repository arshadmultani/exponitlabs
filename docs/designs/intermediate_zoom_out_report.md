# Stage 8 Review Report: Intermediate Zoom Out Review

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** Full Document (Lines 1–453)
* **Target Standard:** [`zoom-out-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/zoom-out-standard.md)
* **Reviewer Persona:** Skeptical Architect (`egm-zoom-out`)
* **Review Date:** 2026-11-02
* **Overall Verdict:** PASS WITH RESERVATIONS

---

## 1. Executive Summary & Architectural Sanity Check

Stage 8 conducts a high-altitude architectural sanity check on the mitigations proposed in Stages 5–7. The review evaluated whether the design solves the territory planning problem simply, avoids speculative complexity (YAGNI), leverages existing codebase conventions, and resolves operational edge cases cleanly before consolidation into the final specification.

### Synthesis on Core Evaluation Questions:

1. **`fromSub()` vs. `selectSub()`/`selectRaw()`**:
   Replacing `fromSub()` with `selectSub()` directly on the base `Doctor::query()` is an unambiguous architectural simplification. In Laravel, pagination automatically strips `selectSub` columns when generating `COUNT(*)`, avoiding double subquery execution. In SQLite, it prevents derived table materialization, allowing table filters (`status`, `area_id`, `doctor_potential_id`) to directly leverage the composite B-tree index.
2. **`DoctorPotential` Admin Resource**:
   Adding a full CRUD resource (`DoctorPotentialResource`) for 4 static business tiers (`Core`, `Serious`, `Nascent`, `Unresponsive`) introduces dangerous complexity creep and operational risk (e.g., accidental tier deletion breaking foreign keys and offline fallback anchors). If an administrative UI is included, it must be strictly constrained (e.g., edit-only with `canCreate = false` and `canDelete = false` to adjust visit targets and colors), or deferred until dynamic tier management is proven necessary.
3. **Tab Partitioning Strictly by Tier**:
   Partitioning tabs strictly by commercial potential tier (`Active Cadence`: non-unresponsive prescribers; `Unresponsive Accounts`: flagged shelved accounts; `All Accounts`: complete directory) successfully eliminates the blindspot where 140 unvisited prospect accounts and severely neglected Core prescribers were hidden in the unresponsive tab. It cleanly separates commercial classification from visit recency.
4. **YAGNI & SQL Simplification**:
   Julian day arithmetic (`julianday(?)`) and the SQL `visit_deficit` calculation are over-engineered artifacts. Sorting by `last_visit_date ASC` (NULLs first) is mathematically and index-wise identical to sorting by longest overdue. Relative date rendering and monthly visit target ratios (`actual / target`) belong in PHP/Filament presentation layer.

---

## 2. Findings & Architectural Rationale

### Finding 1: Query Engine: Replacing `fromSub()` with `selectSub()` is an Essential Simplification
* **Severity:** Blocker
* **Affected Scope:** Section 3.B (Lines 187–203) and Phase 2.6 (Lines 366–376)
* **Simplicity Rationale:**
  The original proposal adopted `Doctor::fromSub($innerQuery, 'doctors')` from `DoctorPortfolioQueryService`. However, `DoctorPortfolioQueryService` required `fromSub()` only because outer arithmetic expressions (`total_investment`, `net_pnl`) combined multiple inner subquery aliases. `DoctorCadenceQueryService` has no such dependency.
  Using `fromSub()` introduces two severe architectural liabilities:
  1. **SQLite Index Invalidation**: Outer Filament filters (`area_id`, `doctor_potential_id`) operate on an anonymous in-memory derived table, forcing full table scans and bypassing the composite index `idx_doctors_cadence_lookup`.
  2. **Pagination Count Overhead**: Filament paginates via `SELECT COUNT(*) FROM (...)`. Under `fromSub()`, SQLite executes all correlated subqueries for every doctor row twice on every request. Under direct `selectSub()`, Laravel's query builder automatically strips `selectSub` columns during pagination count calculation, running a clean `SELECT COUNT(*) FROM doctors WHERE ...` in sub-millisecond time.
  *Remediation:* Build the base query directly on `Doctor::query()`, attaching subqueries for `total_visits_count`, `visits_this_month_count`, and `last_visit_date` via `selectSub()`.

---

### Finding 2: Complexity Creep: Full CRUD `DoctorPotentialResource` is Unnecessary Bloat
* **Severity:** Major
* **Affected Scope:** Section 2.A (Lines 130–150), Alternatives Considered (Lines 273–281), and Phase 1 (Lines 316–326)
* **Simplicity Rationale:**
  Stage 5 highlighted the contradiction of creating a database table without an admin UI. However, adding a full multi-page CRUD resource (`Create`, `Edit`, `List`, `Delete`) for exactly 4 static business tiers (`Core`, `Serious`, `Nascent`, `Unresponsive`) introduces premature abstraction:
  - Pharmaceutical field detailing tiers are foundational business rules; they do not change dynamically day-to-day.
  - Allowing users to create arbitrary tiers breaks visual semantics on the offline field app (ELOS), which expects known tier structures.
  - Allowing users to delete tiers introduces critical integrity failures: deleting the `Nascent` tier breaks foreign key defaults in `SyncController@syncDoctors` and `Doctor::booted()`.
  *Remediation:* Keep tier management minimal. Do not create a bloated multi-page CRUD resource. If a UI is required in v1, implement a simple, lightweight configuration table with creation and deletion disabled (`canCreate = false`, `canDelete = false`), permitting managers only to adjust `target_visits_per_month` and badge colors.

---

### Finding 3: Tab Partitioning Strictly by Tier Resolves Operational Blindspots
* **Severity:** Blocker
* **Affected Scope:** Section 4.B (Lines 104, 216–219) and Phase 4.9 (Lines 402–405)
* **Simplicity Rationale:**
  The original document partitioned Tab 1 ("Active Cadence") by `days_since_last_visit <= 60`. This had disastrous operational side-effects:
  - 140 registered doctors (60% of the entire database) have never been visited (`last_visit_date IS NULL`). They evaluated to `UNKNOWN` in SQL and vanished from the default active planning tab.
  - A high-value `Core` prescriber neglected for 61 days was automatically evicted from the default planning view into "Unresponsive Accounts", hiding the account from management attention.
  Partitioning tabs strictly by commercial potential tier solves this completely:
  - **Active Cadence (Default)**: Prescribers in active commercial tiers (`Core`, `Serious`, `Nascent`, including doctors with null tier). Overdue accounts naturally rise to the top when sorted by `last_visit_date ASC`.
  - **Unresponsive Accounts**: Doctors explicitly tagged with the `Unresponsive` tier.
  - **All Accounts**: Unfiltered directory.
  *Remediation:* Define explicit Eloquent builder scopes for tabs based strictly on `doctor_potential_id` (or `potential.code`), ensuring doctors with `doctor_potential_id IS NULL` remain visible in the default tab.

---

### Finding 4: YAGNI Violations: SQLite Julian Day Math and SQL `visit_deficit` are Redundant
* **Severity:** Major
* **Affected Scope:** Section 3.B (Lines 188–204) and Phase 2.6 (Lines 366–376)
* **Simplicity Rationale:**
  1. **Julian Day Arithmetic:** The spec defines `CAST(julianday(?) - julianday(date(doctors.last_visit_date)) AS INTEGER)` and a `COALESCE(..., 999999)` trick to sort by overdue recency. This is redundant: in SQLite, sorting by `last_visit_date ASC` (where `NULL` sorts first) is mathematically and index-wise identical to sorting by longest overdue. For displaying "14 days ago" across a 25-row paginated page, Carbon's `diffForHumans()` in PHP takes negligible execution time (<0.1ms) and completely avoids SQL timezone binding complexities.
  2. **Unused `visit_deficit` Projection:** The design joins `doctor_potentials` in SQLite to calculate `MAX(0, target_visits_per_month - visits_this_month_count) AS visit_deficit`. This scalar is never displayed, sorted, or filtered in the table. The UI renders `$visits_this_month / $target` using the eager-loaded `potential` relation. Computing it in SQL is dead code.
  *Remediation:* Drop Julian day math and `visit_deficit` from the query service. Sort natively on `last_visit_date ASC/DESC`.

---

### Finding 5: Codebase Reuse & DRY: Unify Schedule Schema Between `DoctorForm` and `editSchedule` Slideover
* **Severity:** Major
* **Affected Scope:** Section 4.E (Lines 248–252) vs Phase 3 (Lines 380–387)
* **Simplicity Rationale:**
  The specification duplicates the exact same form fields (`doctor_potential_id`, `visiting_days`, `visiting_shifts`) in two separate places: `DoctorForm.php` and the `editSchedule` action slideover on `DoctorCadence.php`.
  Defining identical field arrays, options, and helper texts in two files causes schema drift and doubles test maintenance.
  *Remediation:* Extract the schedule field group into a reusable static method on `DoctorForm` (e.g. `DoctorForm::getScheduleSection()` or `DoctorForm::getScheduleComponents()`) and invoke it from both `DoctorForm` and the slideover action.

---

### Finding 6: Data Integrity: Ensure DCR UUID Generation and Empty JSON Array Normalization
* **Severity:** Major
* **Affected Scope:** Section 4.D.3–4 (Lines 239–242), Section 4.E.2 (Lines 253–258), and Models (`Doctor.php`, `DCR.php`)
* **Simplicity Rationale:**
  1. **Empty JSON Arrays:** In Livewire and Filament v5, unchecking all boxes in a `CheckboxList` dehydrates as an empty array `[]` (stored as `'[]'`). Under SQLite, `'[]' IS NULL` evaluates to `FALSE`, breaking the open-availability fallback (`NULL = Everyday`). Normalize empty arrays to `null` in `Doctor::saving()` or via an Eloquent attribute cast.
  2. **DCR UUID Generation:** The `d_c_r_s` table enforces a unique `uuid` constraint required by offline sync (`SyncController`). The `DCR` model currently lacks automatic UUID generation. The `logVisit` action must not create DCR records with null UUIDs. Add a standard `creating()` boot hook on `DCR` to guarantee UUID assignment across all creation paths.

---

### Finding 7: Accurate Nomenclature: Align Column and Field App Route Names
* **Severity:** Nit
* **Affected Scope:** Lines 46, 55, 160, 263
* **Simplicity Rationale:**
  - The specification repeatedly refers to the mobile field app as `/mr`, whereas the actual application routes are registered under `/elos` (`MRDoctorController`, `MRAuthController`).
  - Line 160 references column `'clinic_hospital_name'`, but the actual database column on `doctors` is `'clinic_name'`.
  *Remediation:* Update route references to `/elos` and column references to `'clinic_name'`.

---

## 3. Overall Verdict

**PASS WITH RESERVATIONS**

The underlying architectural concept is sound and directly addresses the core operational problem. The reservations identified above are straightforward to address in Stage 9 (Consolidation & Document Revision):
1. Adopt direct `selectSub()` query attachments instead of `fromSub()`.
2. Avoid full CRUD bloat for `DoctorPotential`; restrict to constrained configuration.
3. Partition tabs strictly by tier to preserve visibility for unvisited accounts.
4. Remove redundant SQLite Julian day math and SQL `visit_deficit`.
5. Share schedule form components via `DoctorForm`.
6. Enforce DCR UUID generation and empty JSON normalization.
