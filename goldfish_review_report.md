# Goldfish Annotated Review Report

**Target:** `docs/designs/doctor_cadence.md` (full file, lines 1–453)  
**Status:** resolved (All 19 Propose Fix items accepted and applied)  
**Evaluated by:** EGM Review Swarm (`egm-zoom-out`, `egm-comprehension`, `egm-design`, `egm-structural`, `egm-style`, `egm-implementation-readiness`, `egm-codebase-details`)  
**Resolution Agent:** Exponit Elephant (`egm-resolve`)  
**Date:** 2026-11-02

---

## Prioritized Findings & AI Suggestions

### Issue 1 [Hostile Design Critic / Structural Validator] - Severity: High (Blocker)
* **Affected Scope:** Document Root / Pre-Implementation Section (`docs/designs/doctor_cadence.md#L309`)
* **Discrepancy Description:** Missing mandatory blueprint section: `## Risks & Limits` / `## Risks & Mitigations` required by `feature-spec-standard.md`.
* **Rationale:** The standard mandates identifying failure modes, concurrency/locking risks under SQLite during offline sync, timezone drift, and query scalability boundaries.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** The critique is accurate. Adding a dedicated `## Risks & Mitigations` section before `## Detailed Implementation Plan` fulfills the standard and documents production concurrency and date boundary defenses.
* ***Proposed Edit:***
  * Location: Insert before `## Detailed Implementation Plan` (Line 309)
  * Change: Add `## Risks & Mitigations` covering SQLite WAL concurrency, local IST date binding, and subquery index optimization.

---

### Issue 2 [Hostile Design Critic / Codebase Auditor] - Severity: High (Blocker)
* **Affected Scope:** Section 3 (Lines 174–203) & Phase 2.6 (Lines 366–376)
* **Discrepancy Description:** 
  1. `Doctor::fromSub()` forces SQLite to materialize an in-memory unindexed derived table, invalidating the composite index on Filament filters and doubling subquery execution during pagination counts.
  2. Line 370 specifies a correlated subquery returning 3 columns simultaneously, violating scalar subquery cardinality (`SQLSTATE[21000]: 1241 Operand should contain 1 column(s)`).
* **Rationale:** Subqueries attached directly via `selectSub()` on `Doctor::query()` preserve table context, enable predicate pushdown, and are automatically stripped by Laravel during pagination counts.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Direct `selectSub()` attachments represent a cleaner, faster, and syntactically valid query architecture.
* ***Proposed Edit:***
  * Location: Section 3.B and Phase 2.6
  * Change: Replace `fromSub()` with base `Doctor::query()` attaching 3 separate scalar `selectSub()` queries (`total_visits_count`, `visits_this_month_count`, `last_visit_date`).

---

### Issue 3 [Hostile Design Critic / Naive Reader / Skeptical Architect] - Severity: High (Blocker)
* **Affected Scope:** Section 4.B (Lines 104, 216–219) & Phase 4.9 (Lines 402–405)
* **Discrepancy Description:** Partitioning the default "Active Cadence" tab by `days_since_last_visit <= 60` causes all 140 unvisited accounts (`days_since_last_visit IS NULL`) to vanish, and automatically purges high-value Core doctors once they reach 61 days overdue.
* **Rationale:** Account commercial tier must not be conflated with visit recency. The default tab must surface all active territory accounts, sorting overdue prescribers to the top.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Validating tab membership strictly by tier (`doctor_potentials.code != 'unresponsive'`) completely solves the operational blindspot.
* ***Proposed Edit:***
  * Location: Section 4.B and Phase 4.9
  * Change: Define `Active Cadence` as `code != 'unresponsive'` (or `doctor_potential_id IS NULL`), `Unresponsive Accounts` as `code = 'unresponsive'`, and `All Accounts` as unfiltered.

---

### Issue 4 [Naive Reader / Tech Lead / Codebase Auditor] - Severity: High (Blocker)
* **Affected Scope:** Section 2.A (Lines 144–150) vs Phase 1.3 (Lines 338–341) & Line 323
* **Discrepancy Description:** Inverted commercial tier UI color tokens. Section 2.A defines Core as `success`, whereas Phase 1.3 seeder defines Core as `danger` (and Unresponsive as `gray` vs `danger`).
* **Rationale:** Contradictory token definitions will lead to inconsistent UI rendering and badge semantics across the console.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Core high-prescribing doctors should use `success` (Emerald Green) while Unresponsive dormant accounts use `gray` or `danger`.
* ***Proposed Edit:***
  * Location: Section 2.A and Phase 1.3
  * Change: Harmonize tokens deterministically: `Core => success`, `Serious => info`, `Nascent => warning`, `Unresponsive => gray`.

---

### Issue 5 [Tech Lead / Codebase Auditor] - Severity: High (Blocker)
* **Affected Scope:** Section 4.A (Lines 208–215), Phase 4.9 (Lines 397–423), Phase 4.10 (Lines 424–427)
* **Discrepancy Description:** Specifying `HasTabs` contract and rendering `{{ $this->table }}` in Blade violates Filament v5 conventions and completely prevents header tabs from rendering.
* **Rationale:** In Filament v5, pages embedding tables implement `HasTable`, use `HasTabs`, define `content(Schema $schema): Schema` returning `[$this->getTabsContentComponent(), EmbeddedTable::make()]`, and render `{{ $this->content }}` in Blade.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Codebase contracts in `DoctorPortfolio.php` already establish this exact pattern.
* ***Proposed Edit:***
  * Location: Section 4.A, Phase 4.9, and Phase 4.10
  * Change: Update class signature to `Page implements HasTable`, implement `content(Schema $schema): Schema`, and render `{{ $this->content }}` in Blade.

---

### Issue 6 [Tech Lead] - Severity: High (Blocker)
* **Affected Scope:** Phase 1.3 (Lines 335–344) & Phase 2.4 (Lines 349–355)
* **Discrepancy Description:** Topological dependency inversion: Phase 1 item 3 creates `DoctorPotentialSeeder.php` calling `DoctorPotential::firstOrCreate()`, but `DoctorPotential.php` is not created until Phase 2.
* **Rationale:** Running Phase 1 migrations and seeders in CI or development throws a fatal class-not-found error.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Moving domain model creation to Phase 1 restores clean topological dependency order.
* ***Proposed Edit:</a>
  * Location: Reorder Phase 1 and Phase 2 items.
  * Change: Schedule `DoctorPotential.php` and `Doctor.php` modifications in Phase 1 before running `DoctorPotentialSeeder`.

---

### Issue 7 [Hostile Design Critic / Codebase Auditor / Skeptical Architect] - Severity: Medium (Major)
* **Affected Scope:** Section 4.D.3–4 (Lines 237–244), Phase 2.5 (Lines 362–363), Phase 3.7 (Lines 385–386)
* **Discrepancy Description:** In Filament and Livewire v4, deselecting all checkboxes in `CheckboxList` dehydrates to `[]` (stored as `'[]'` string in SQLite), not SQL `NULL`, breaking the open-availability fallback (`NULL = Everyday`).
* **Rationale:** Unchecking all boxes renders the doctor permanently unavailable in day and shift filters.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Normalizing empty arrays to `null` on form save and updating scopes guarantees robust open-door filtering.
* ***Proposed Edit:***
  * Location: `DoctorForm.php` and `Doctor.php` scopes
  * Change: Add `dehydrateStateUsing(fn ($state) => empty($state) ? null : array_values($state))` and update query scopes to include `orWhere('visiting_days', '[]')`.

---

### Issue 8 [Hostile Design Critic / Codebase Auditor / Skeptical Architect] - Severity: Medium (Major)
* **Affected Scope:** Section 2.B (Line 160) & Phase 1.2 (Line 327)
* **Discrepancy Description:** Non-existent column reference: migration specifies `after('clinic_hospital_name')`, but the actual column is `clinic_name`.
* **Rationale:** Strict database drivers will throw a column-not-found error during migration execution.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Align migration code with the actual `doctors` table schema.
* ***Proposed Edit:***
  * Location: Section 2.B and Phase 1.2
  * Change: Change `after('clinic_hospital_name')` to `after('clinic_name')`.

---

### Issue 9 [Hostile Design Critic / Skeptical Architect] - Severity: Medium (Major)
* **Affected Scope:** Section 4.E.2 (Lines 253–258) & Section 5 (Lines 261–267)
* **Discrepancy Description:** Console `logVisit` action inserts directly into `d_c_r_s` without generating a UUID, breaking client-side Dexie.js offline reconciliation keys and risking duplicate records.
* **Rationale:** `d_c_r_s` enforces unique UUID constraints that mobile synchronization relies on.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Adding a `booted()` creating hook on `DCR` guarantees UUID generation across both console and API paths.
* ***Proposed Edit:***
  * Location: `app/Models/DCR.php` and Section 4.E.2
  * Change: Specify `booted()` creating hook assigning `(string) Str::uuid()`, and add duplicate date validation in `logVisit`.

---

### Issue 10 [Hostile Design Critic / Codebase Auditor / Tech Lead] - Severity: Medium (Major)
* **Affected Scope:** Section 5 (Lines 263–264) & Phase 5.11 (Lines 432–438)
* **Discrepancy Description:** `SyncController::syncDoctors()` lacks `$request->validate()` rules and attribute mapping for `doctor_potential_id`, `visiting_days`, and `visiting_shifts`, while `masterData()` sends an isolated integer ID without offline tier metadata.
* **Rationale:** Newly registered offline doctors would fail validation or discard schedule data, and the mobile app cannot render tier badges offline without metadata.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Explicit validation rules and denormalized tier metadata in master data resolve offline contracts cleanly.
* ***Proposed Edit:***
  * Location: Section 5 and Phase 5.11
  * Change: Add validation rules, map attributes in `Doctor::updateOrCreate()`, and denormalize tier metadata in `masterData()`.

---

### Issue 11 [Codebase Auditor] - Severity: Medium (Major)
* **Affected Scope:** Phase 2 (Line 349) & Phase 6 (Line 443)
* **Discrepancy Description:** Missing `database/factories/DoctorPotentialFactory.php`, violating `AGENTS.md` guidelines and complicating Pest test setup.
* **Rationale:** Every new Eloquent model requires an accompanying factory for isolated testing.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Create the factory in Phase 2.
* ***Proposed Edit:***
  * Location: Phase 2
  * Change: Add `database/factories/DoctorPotentialFactory.php` [Create].

---

### Issue 12 [Skeptical Architect] - Severity: Medium (Major)
* **Affected Scope:** Section 3.B (Lines 188–204) & Phase 2.6 (Lines 366–376)
* **Discrepancy Description:** Over-engineered SQL Julian day math and unused `visit_deficit` calculation.
* **Rationale:** Sorting by `last_visit_date ASC NULLS FIRST` achieves identical ordering natively in SQLite, while Carbon in PHP formats relative dates in <0.1ms.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Stripping dead SQL projections simplifies the query service and avoids timezone edge cases.
* ***Proposed Edit:***
  * Location: Section 3.B and Phase 2.6
  * Change: Drop SQL `visit_deficit` and Julian day math; sort natively by `last_visit_date`.

---

### Issue 13 [Skeptical Architect] - Severity: Medium (Major)
* **Affected Scope:** Section 4.E (Lines 248–252) vs Phase 3 (Lines 380–387)
* **Discrepancy Description:** Duplicated form schema between `DoctorForm.php` and the `editSchedule` action slideover.
* **Rationale:** Defining identical field lists in two separate files creates schema drift and doubles maintenance.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Reusable schema methods preserve DRY principles across Filament components.
* ***Proposed Edit:***
  * Location: Phase 3 and Phase 4
  * Change: Extract schedule fields into `DoctorForm::getScheduleComponents()` and invoke it from both locations.

---

### Issue 14 [Tech Lead] - Severity: Medium (Major)
* **Affected Scope:** Phase 6 (Lines 440–453)
* **Discrepancy Description:** Missing targeted test runner commands in Phase 6.
* **Rationale:** Engineers and automated CI harnesses require explicit Artisan/Pest commands.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Document the exact verification commands.
* ***Proposed Edit:***
  * Location: Phase 6
  * Change: Add `php artisan test --compact tests/Feature/DoctorCadenceTest.php` and full regression command.

---

### Issue 15 [Technical Copy Editor] - Severity: Medium (Major)
* **Affected Scope:** Lines 45, 46, 296, 312, 433, 443
* **Discrepancy Description:** Unmeasured performance hyperbole ("sub-millisecond"), marketing buzzwords ("effortless", "seamless", "zero-friction"), and absolute claims ("flawless", "100% feature reliability").
* **Rationale:** Violates the plain-English, anti-fluff standard required by EGM.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Replace with objective, professional engineering descriptions.
* ***Proposed Edit:***
  * Location: Throughout lines 1–453
  * Change: Apply copy editorial replacements.

---

### Issue 16 [Naive Reader / Tech Lead] - Severity: Low (Nit)
* **Affected Scope:** Section 2.A (Line 138), Phase 1.1 (Lines 321–325), Phase 2.4 (Line 352)
* **Discrepancy Description:** Missing `is_active` column in Phase 1 key columns, `$fillable`, and `$casts`.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Complete the attribute definition.
* ***Proposed Edit:***
  * Location: Phase 1.1 and `DoctorPotential.php`
  * Change: Include `is_active` in migration columns, `$fillable`, and `$casts`.

---

### Issue 17 [Naive Reader / Skeptical Architect] - Severity: Low (Nit)
* **Affected Scope:** Overview (Line 14) and throughout
* **Discrepancy Description:** Undefined acronyms (DCR, OPD, Beat) and mobile app route referencing `/mr` instead of `/elos`.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Clarify terms and align routes with codebase conventions.
* ***Proposed Edit:***
  * Location: Overview, Problem, and Non-Goals
  * Change: Spell out acronyms and update route references.

---

### Issue 18 [Codebase Auditor] - Severity: Low (Nit)
* **Affected Scope:** Phase 3.8 (Line 391)
* **Discrepancy Description:** Potential N+1 query on `DoctorsTable.php` when rendering potential badges across 200 rows.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Eager-load the relationship to guarantee single-query rendering.
* ***Proposed Edit:</a>
  * Location: Phase 3.8
  * Change: Add `modifyQueryUsing(fn ($query) => $query->with('potential'))`.

---

### Issue 19 [Structural Validator] - Severity: Low (Nit)
* **Affected Scope:** Lines 99, 104 (`### 1. Architectural System Overview`)
* **Discrepancy Description:** Mermaid diagram contains unescaped `<` and `>` operators in node labels.
* ***AI Suggestion:*** `PROPOSE FIX`
* ***Justification:*** Prevent SVG parsing issues in strict Markdown parsers.
* ***Proposed Edit:***
  * Location: Line 104
  * Change: Replace with `&le;` and `&gt;`.
