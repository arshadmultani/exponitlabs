# Stage 6 Review Report: Comprehension Test Audit

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** Full Document (FeatureRange lines 1–309 & DetailedRange lines 310–453)
* **Target Standard:** [`re-implementation-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/re-implementation-standard.md)
* **Reviewer Persona:** Naive Reader (`egm-comprehension`)
* **Review Date:** 2026-11-02
* **Overall Verdict:** FAIL

---

## 1. Executive Summary

The specification exhibits high architectural ambition but violates the Re-Implementation Zero-Assumption Rule across three critical areas:
1. **Direct Internal Contradictions**: Color tokens are inverted between schema definitions and seeder instructions; Julian day math uses parameter-bound dates in architecture but local string clocks in the implementation plan.
2. **Boundary Condition Void**: 140 never-visited doctors (60% of the entire territory) are excluded from the default planning tab due to unhandled `NULL` Julian day comparisons and undefined filtering phrases.
3. **Ghost References & Missing Joins**: References non-existent columns (`clinic_hospital_name`), omits required SQL joins to `doctor_potentials`, and fails to specify UUID generation contracts required by mobile sync.

---

## 2. Findings & Comprehension Defects

### Finding 1: Blatant Contradiction in Commercial Potential Tier UI Color Tokens
* **Severity:** Blocker
* **Location:** Section 2.A (Lines 144–150) vs Phase 1.3 (Lines 338–341) & Line 323
* **Defect:**
  - Section 2.A defines: `Core = success`, `Serious = info`, `Nascent = warning`, `Unresponsive = danger`.
  - Phase 1.3 seeder and Line 323 define: `Core = danger`, `Serious = warning`, `Nascent = info`, `Unresponsive = gray`.
  An engineer implementing this directly will build conflicting seeders and badges depending on which section is followed.
* **Remediation:** Harmonize color mapping to a single deterministic standard across both tables and seeders (Core: `danger` or `success`, Serious: `warning` or `info`, Nascent: `info` or `warning`, Unresponsive: `gray`).

---

### Finding 2: Tab Filtering Rules Drop 140 Never-Visited Doctors (60% of Database)
* **Severity:** Blocker
* **Location:** Section 4.B (Lines 217–219) & Phase 4.9 (Lines 403–405)
* **Defect:**
  - Tab 1 ("Active Cadence") is defined as `days_since_last_visit <= 60` or "active detailing relationships".
  - Tab 2 ("Unresponsive Accounts") is defined as `Unresponsive` or unvisited for `>60` days "with high deficits".
  For the 140 unvisited doctors, `last_visit_date` is `NULL`, making `days_since_last_visit` `NULL`. In SQL, both `NULL <= 60` and `NULL > 60` evaluate to `UNKNOWN` (false). Furthermore, "active detailing relationships" and "with high deficits" have no corresponding database columns or integer thresholds defined. Consequently, unvisited accounts vanish entirely from Tab 1.
* **Remediation:** Provide explicit Eloquent builder closures for every tab. Explicitly define where unvisited accounts reside:
  - Tab 1 (`active`): `$query->where(fn ($q) => $q->where('days_since_last_visit', '<=', 60)->orWhereNull('days_since_last_visit'))->where('doctor_potentials.code', '!=', 'unresponsive')`.
  - Tab 2 (`unresponsive`): `$query->where('doctor_potentials.code', '=', 'unresponsive')->orWhere('days_since_last_visit', '>', 60)`.
  Eliminate vague descriptors ("with high deficits", "active detailing relationships").

---

### Finding 3: Non-Existent Column Reference in Migration Code
* **Severity:** Blocker
* **Location:** Section 2.B (Line 160)
* **Defect:**
  `$table->json('visiting_days')->nullable()->after('clinic_hospital_name');`
  In the actual database schema and Eloquent model for `doctors`, the clinic name column is named `clinic_name`, not `clinic_hospital_name`. Executing or porting this migration as written references a non-existent column.
* **Remediation:** Change `'clinic_hospital_name'` to `'clinic_name'` in the migration specification.

---

### Finding 4: Internal Contradiction in Julian Day Parameter Binding
* **Severity:** Major
* **Location:** Section 3.B (Lines 191–193) vs Phase 2.6 (Line 371)
* **Defect:**
  - Section 3.B mandates: `CAST(julianday(?) - julianday(date(doctors.last_visit_date)) AS INTEGER) AS days_since_last_visit`, explicitly noting that `?` receives `Carbon::today()->toDateString()` in local IST to prevent UTC midnight drift.
  - Phase 2.6 contradicts this: `Computes CAST(julianday('now', 'localtime') - julianday(last_visit_date) AS INTEGER) AS days_since_last_visit`.
  In containerized/cloud environments where the system clock runs in UTC, `julianday('now', 'localtime')` ignores Laravel's application timezone and triggers the exact date drift the document warned against.
* **Remediation:** Update Phase 2.6 to match Section 3.B, using parameter-bound `julianday(?)` with `Carbon::today()->toDateString()`.

---

### Finding 5: Missing Join Specification for `doctor_potentials` in Query Service
* **Severity:** Major
* **Location:** Section 3.B (Lines 187–203) & Phase 2.6 (Lines 368–375)
* **Defect:**
  Section 3.B defines the outer projection as `Doctor::query()->fromSub($innerQuery, 'doctors')` and calculates `MAX(0, COALESCE(doctor_potentials.target_visits_per_month, 1) - doctors.visits_this_month_count) AS visit_deficit`. However, Section 3.B omits the join clause to `doctor_potentials`. Referencing `doctor_potentials.target_visits_per_month` without joining produces a SQLite `no such column: doctor_potentials.target_visits_per_month` error.
* **Remediation:** Explicitly specify the outer query structure including `->leftJoin('doctor_potentials', 'doctor_potentials.id', '=', 'doctors.doctor_potential_id')`.

---

### Finding 6: Unhandled Empty JSON Array `[]` in Availability Schedule Queries
* **Severity:** Major
* **Location:** Section "Goals" (Line 33), Section 4.D.3–4 (Lines 239–242), Phase 2.5 (Lines 362–363)
* **Defect:**
  Filament's `CheckboxList` dehydrates an unselected state as an empty array `[]` (stored as `'[]'` in JSON), rather than SQL `NULL`. Because `whereJsonContains('visiting_days', 'wednesday')` evaluates to false on `'[]'`, and `'[]'` is not null (`whereNull` is false), unchecking all boxes renders the doctor permanently unavailable across all day and shift filters.
* **Remediation:** Specify that a model mutator / saving hook on `Doctor` normalizes empty arrays to `null` (`empty($val) ? null : $val`), or update the query scope to evaluate:
  `$q->whereJsonContains('visiting_days', $day)->orWhereNull('visiting_days')->orWhere('visiting_days', '[]')`.

---

### Finding 7: Missing Query Definitions for "Cadence Urgency" Filter
* **Severity:** Major
* **Location:** Section 4.D.5 (Line 245) & Phase 4.9 (Line 422)
* **Defect:**
  Options `Never Visited`, `Overdue (>30d)`, and `Severely Overdue (>60d)` are listed without defining their underlying query logic.
* **Remediation:** Provide explicit query closures for each option:
  - `never_visited`: `whereNull('doctors.last_visit_date')`
  - `overdue_30`: `where('days_since_last_visit', '>', 30)` (stating whether unvisited are included)
  - `overdue_60`: `where('days_since_last_visit', '>', 60)`.

---

### Finding 8: Undefined Algorithmic Rules for Column Color States and Availability Tokens
* **Severity:** Major
* **Location:** Section 4.C (Lines 226, 231) & Phase 4.9 (Line 413)
* **Defect:**
  - Line 226 states: "`visits_this_month`... colored warning if deficient mid-month, success if target met." There is no formula for "deficient mid-month".
  - Line 231 states: "`availability`: Compact single-line token. If null or full schedule, displays emerald badge: Everyday (All Shifts)..." Formatting rules are unstated.
* **Remediation:** Define exact closure logic for badge coloring and token formatting.

---

### Finding 9: Index Signature Mismatch on `doctors`
* **Severity:** Major
* **Location:** Section 2.B (Line 164) vs Phase 1.2 (Line 333)
* **Defect:** Line 164 includes `status` in the composite index, while Phase 1.2 line 333 omits `status`.
* **Remediation:** Harmonize index definition to include `['status', 'doctor_potential_id', 'area_id']`.

---

### Finding 10: Missing `is_active` Column in Model `$fillable`
* **Severity:** Major
* **Location:** Section 2.A (Line 138), Phase 1.1 (Lines 321–325), Phase 2.4 (Line 352)
* **Defect:** Section 2.A adds `$table->boolean('is_active')->default(true)->index();`, but Phase 1.1 omits it from key columns, and Phase 2.4 omits it from `DoctorPotential::$fillable`.
* **Remediation:** Include `is_active` in Phase 1.1 key columns, `$fillable`, and `$casts`.

---

### Finding 11: Contradiction Regarding Nullable vs NOT NULL Constraint on `doctor_potential_id`
* **Severity:** Major
* **Location:** Lines 46, 154–158, 266, 330, 436
* **Defect:** Lines 46, 266, and 436 state that defaulting offline doctors to `Nascent` is required to "prevent SQLite NOT NULL constraint crashes", whereas lines 154–158 define the foreign key as `nullable()`.
* **Remediation:** Clarify that defaulting to `Nascent` is an operational business requirement.

---

### Finding 12: Missing UUID Generation in `logVisit` Action
* **Severity:** Major
* **Location:** Section 4.E.2 (Lines 253–258) & Phase 4.9 (Line 416)
* **Defect:** `d_c_r_s` table has a unique `uuid` column (`d_c_r_s_uuid_unique`). The `DCR` model has no automatic UUID generation in its `booted()` method. If `logVisit` directly inserts `date` and `remarks` into `d_c_r_s` without generating a UUID, sync integrity is broken.
* **Remediation:** Specify that `logVisit` supplies `'uuid' => (string) Str::uuid()`, or specify a `booted()` model hook on `DCR`.

---

### Finding 13: Undefined Acronyms & Ungrounded Jargon
* **Severity:** Nit
* **Location:** Section "Overview" (Line 14) and throughout
* **Defect:** Undefined acronyms: "DCR" (Daily Call Report), "OPD" (Outpatient Department), "Beat" (route cluster).
* **Remediation:** Spell out acronyms upon first appearance.
