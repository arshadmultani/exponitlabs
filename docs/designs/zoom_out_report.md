# Stage 5 Review Report: Initial Zoom Out Review

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** Full File (Lines 1–453)
* **Target Standard:** [`zoom-out-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/zoom-out-standard.md)
* **Reviewer Persona:** Skeptical Architect (`egm-zoom-out`)
* **Review Date:** 2026-11-02
* **Overall Verdict:** PASS WITH RESERVATIONS

---

## 1. Executive Summary

The specification establishes a cohesive operational framework for transitioning physician visit management from subjective, ad-hoc planning to structured, cadence-driven route execution across the territory's 233 registered healthcare providers. Problem framing, root cause analysis, and target operational benefits are clearly articulated. However, the architectural design contains critical failure modes regarding empty JSON array dehydration in Filament forms, conflation of unvisited prospect accounts with shelved accounts in tab partitioning, unmanaged database lookup tables without administrative interfaces, and over-engineered SQL math that duplicates Eloquent capabilities.

---

## 2. Findings & Architectural Critique

### Finding 1: JSON Availability Fallback Breaks Under Filament Form Lifecycle
* **Severity:** Blocker
* **Affected Scope:** Section 4.D (Lines 237–244), Section 5 (Lines 362–363), and Phase 3 (Lines 385–386)
* **Simplicity Rationale:** 
  The proposal relies on the convention that an empty schedule denotes open availability (`NULL = Everyday (All Shifts)`), with query filters structured as:
  ```php
  $q->whereJsonContains('visiting_days', $day)->orWhereNull('visiting_days');
  ```
  In Livewire and Filament v5, unselected checkboxes in a `CheckboxList` dehydrate as an empty PHP array (`[]`), which Eloquent serializes into SQLite as `'[]'` (a valid non-null empty JSON array string). In SQLite, `'[]' IS NULL` evaluates to `FALSE`, and `json_contains('[]', 'wednesday')` evaluates to `FALSE`.
  
  Any doctor edited or saved via the Filament form with all checkboxes cleared will store `visiting_days = '[]'`, causing them to silently disappear from all day-of-week and shift route queries. CUJ 1 will fail for every touched record.
* **Remediation:** 
  Add an Eloquent mutator or Attribute cast on `Doctor` that normalizes empty arrays to `null` before saving (`$doctor->visiting_days = empty($value) ? null : $value;`), and update query scopes to explicitly match empty array strings:
  ```php
  $q->whereJsonContains('visiting_days', $day)
    ->orWhereNull('visiting_days')
    ->orWhere('visiting_days', '[]')
    ->orWhere('visiting_days', '');
  ```

---

### Finding 2: Tab Logic Conflates Operational Neglect with Shelved Accounts
* **Severity:** Major
* **Affected Scope:** Section 4.B (Lines 104, 216–219) and Phase 4 (Lines 402–405)
* **Simplicity Rationale:**
  The design specifies:
  - Tab 1 (`active`): Accounts with `days_since_last_visit <= 60`
  - Tab 2 (`unresponsive`): Accounts flagged as `Unresponsive` or unvisited for >60 days
  
  The problem statement specifically notes that 140 of the 233 registered doctors have never been visited (`last_visit_date IS NULL`). Under the proposed logic, unvisited doctors have a null or 999,999-day overdue calculation (>60 days), meaning **all 140 unvisited doctors will be dumped into the "Unresponsive Accounts" tab**.
  
  Newly registered prospect accounts awaiting an initial rep visit will be hidden from the default `active` route planning tab and incorrectly branded as "unresponsive". Tab logic must separate commercial account status (e.g., accounts explicitly tagged with the `Unresponsive` tier) from operational cadence urgency (e.g., active accounts that are overdue or never visited).
* **Remediation:** Partition tabs strictly by tier:
  - **Active Cadence (Default)**: `doctor_potentials.code != 'unresponsive'` (including all overdue and never-visited accounts).
  - **Unresponsive Accounts**: `doctor_potentials.code = 'unresponsive'`.
  - **All Accounts**: Unfiltered.

---

### Finding 3: Database Table Without Management UI (Architectural Inconsistency)
* **Severity:** Major
* **Affected Scope:** Section 2.A (Lines 130–150), Alternatives Considered (Lines 273–281), and Phase 1 (Lines 316–326)
* **Simplicity Rationale:**
  Alternative 1 rejects a PHP Enum because "the user explicitly requested database storage over hardcoded enums so potential tiers can be modified, reconfigured, or extended dynamically in the database without requiring code deployments."
  
  However, nowhere in the technical plan (Phases 1–6) is there an administrative interface, Filament Resource, or Settings page to manage, create, or update `DoctorPotential` records. Without an admin UI, modifying tiers, visit targets, or colors still requires a developer to write seeders or run SQL commands. The system assumes the architectural overhead of a relational database table (foreign keys, migrations, seeders, sync serialization, and join queries) without delivering self-serve flexibility.
* **Remediation:** Add a lightweight `DoctorPotentialResource` in Filament under a Settings/Configuration cluster so operations managers can actually configure tiers without code deployments.

---

### Finding 4: Over-Engineered SQL Math and Redundant Projections
* **Severity:** Major
* **Affected Scope:** Section 3.B (Lines 188–204) and Phase 2 (Lines 366–376)
* **Simplicity Rationale:**
  1. **Speculative `visit_deficit` Join:** The query engine joins `doctor_potentials` in SQLite to project `MAX(0, target_visits_per_month - visits_this_month_count) AS visit_deficit`. However, `visit_deficit` is never used as a sortable column, filter, or displayed metric in the table (the table uses eager-loaded `potential` to render `actual / target`). Joining `doctor_potentials` just to compute an unused scalar in SQL violates YAGNI.
  2. **Julian Day Arithmetic vs. Native Date Sorting:** Projecting `days_since_last_visit` and `sortable_days_overdue` via SQLite Julian day math with parameter-bound local timestamps is unnecessary for sorting. Sorting by longest overdue is index-wise and monotonically identical to sorting by `last_visit_date ASC` (NULLs first). For table display across a paginated 25–50 row page, Carbon's `diffInDays()` or `diffForHumans()` in PHP executes in <0.1ms without SQL date-math overhead.
* **Remediation:** Remove unused `visit_deficit` SQL projections. Rely on native `last_visit_date` index sorting.

---

### Finding 5: Re-Inventing Form Schema Across Filament Components
* **Severity:** Major
* **Affected Scope:** Section 4.E (Lines 248–252) and Phase 4 (Lines 414–416)
* **Simplicity Rationale:**
  The `editSchedule` slideover action duplicates the exact same form fields (`doctor_potential_id`, `visiting_days`, `visiting_shifts`) configured in `DoctorForm.php`. Defining identical field lists in two separate files creates schema drift when field rules or options change.
* **Remediation:** Extract schedule fields into a reusable static method on `DoctorForm` (e.g., `DoctorForm::getScheduleSection()`) and call it from both `DoctorForm` and the `editSchedule` action slideover.

---

### Finding 6: Non-Existent Schema Column Reference
* **Severity:** Major
* **Affected Scope:** Section 2.B (Line 160) and Phase 1 (Line 327)
* **Simplicity Rationale:**
  Line 160 defines `$table->json('visiting_days')->nullable()->after('clinic_hospital_name')`. The actual column on the `doctors` table is `clinic_name`. This propagates a legacy naming discrepancy and will trigger migration exceptions on strict SQL drivers.
* **Remediation:** Correct `'clinic_hospital_name'` to `'clinic_name'`.

---

### Finding 7: Contradictory Technical Rationale for Offline Synchronization
* **Severity:** Nit
* **Affected Scope:** Goal 6 (Line 46) and Phase 5 (Line 436)
* **Simplicity Rationale:**
  The document states that defaulting offline doctors to `Nascent` is necessary to prevent "SQLite NOT NULL constraint crashes". However, the migration in Section 2.B explicitly defines `doctor_potential_id` as `->nullable()`. While defaulting newly created doctors to `Nascent` is sound business logic, the technical justification is incorrect.
* **Remediation:** State that defaulting to `Nascent` is an operational business requirement to ensure every active account possesses a baseline visit target for cadence calculations.

---

### Finding 8: Premature Detail & Altitude Drops
* **Severity:** Nit
* **Affected Scope:** Sections 2, 3, and Detailed Implementation Plan (Lines 130–167, 190–203, 338–342)
* **Simplicity Rationale:**
  The document frequently drops into code-level weeds, writing out verbatim Blueprint closures, SQL strings, and seeder calls, while glossing over critical data-flow subtleties.
* **Remediation:** Focus architecture sections on lifecycle boundaries, contracts, and schema responsibilities.
