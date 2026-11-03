# Consolidated Review Report: Doctor Call Cadence & Route Planning System

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** Complete Specification (Lines 1–453: Feature Spec & Implementation Plan)
* **Review Protocol:** Elephant-Goldfish Model (EGM) Multi-Perspective Review Swarm
* **Review Date:** 2026-11-02
* **Overall Swarm Verdict:** **FAIL (Actionable Remediation Required)**

---

## 1. Executive Summary & Swarm Scorecard

The review swarm completed an exhaustive evaluation of the Doctor Call Cadence & Route Planning specification across all five stages of the EGM protocol. The reviewers affirmed the core domain model—integrating database-backed potential tiers with clinic availability schedules to eliminate ad-hoc, mood-driven territory visit planning.

However, the swarm identified **6 Blocker defects** and **9 Major architectural issues** that would cause compile-time failures, fatal SQL exceptions, data loss during offline synchronization, and severe operational blindspots if built as specified.

| Stage | Focus Area | Reviewer Persona | Verdict | Report Link |
| :--- | :--- | :--- | :--- | :--- |
| **Stage 5** | Initial Altitude, Scope & YAGNI | Skeptical Architect (`egm-zoom-out`) | `PASS WITH RESERVATIONS` | [`zoom_out_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/zoom_out_report.md) |
| **Stage 6** | Zero-Assumption Comprehension | Naive Reader (`egm-comprehension`) | `FAIL` | [`comprehension_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/comprehension_report.md) |
| **Stage 7** | System Architecture & Risk | Hostile Design Critic (`egm-design`) | `FAIL` | [`critic_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/critic_report.md) |
| **Stage 7** | Structural Layout & Standards | Layout Validator (`egm-structural`) | `PASS WITH RESERVATIONS` | [`critic_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/critic_report.md) |
| **Stage 7** | Technical Style & Precision | Copy Editor (`egm-style`) | `PASS WITH RESERVATIONS` | [`critic_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/critic_report.md) |
| **Stage 8** | Intermediate Architectural Sanity | Skeptical Architect (`egm-zoom-out`) | `PASS WITH RESERVATIONS` | [`intermediate_zoom_out_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/intermediate_zoom_out_report.md) |
| **Stage 9** | Codebase Alignment & Verification | Codebase Auditor (`egm-codebase-details`) | `FAIL` | [`implementation_readiness_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/implementation_readiness_report.md) |
| **Stage 9** | Pragmatic Execution & Contracts | Tech Lead (`egm-implementation-readiness`) | `FAIL` | [`implementation_readiness_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/implementation_readiness_report.md) |
| **Stage 9** | Implementation Style & Clarity | Copy Editor (`egm-style`) | `PASS WITH RESERVATIONS` | [`implementation_readiness_report.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/implementation_readiness_report.md) |

---

## 2. Traceability Matrix

The following matrix maps raw findings across all 9 reviewer evaluations into unified, consolidated action items:

| Action Item | Severity | Originating Stages & Reviewers | Core Issue Addressed |
| :--- | :--- | :--- | :--- |
| **ACT-01** | 🔴 **Blocker** | Stage 7 (Design Critic, Structural) | Missing mandatory blueprint section: `## Risks & Mitigations`. |
| **ACT-02** | 🔴 **Blocker** | Stage 7 (Design), Stage 8 (Zoom Out), Stage 9 (Codebase Details) | Replace `fromSub()` with `selectSub()`; fix multi-column scalar subquery cardinality violation. |
| **ACT-03** | 🔴 **Blocker** | Stage 6 (Comprehension), Stage 7 (Design), Stage 8 (Zoom Out) | Partition tabs strictly by tier (`code != 'unresponsive'`) to prevent 140 unvisited accounts from vanishing. |
| **ACT-04** | 🔴 **Blocker** | Stage 6 (Comprehension), Stage 9 (Tech Lead, Codebase Details) | Harmonize commercial potential tier UI color tokens across schema, UI, and seeders. |
| **ACT-05** | 🔴 **Blocker** | Stage 9 (Tech Lead, Codebase Details) | Fix Filament v5 Page contracts and Blade view: implement `HasTable`, use `HasTabs`, render `{{ $this->content }}`. |
| **ACT-06** | 🔴 **Blocker** | Stage 9 (Tech Lead) | Fix topological inversion: move `DoctorPotential` model creation to Phase 1 before seeder execution. |
| **ACT-07** | 🟡 **Major** | Stage 5 (Zoom Out), Stage 6 (Comprehension), Stage 7 (Design), Stage 9 (Codebase Details) | Prevent empty JSON array dehydration (`[]`) from breaking open-availability route filtering. |
| **ACT-08** | 🟡 **Major** | Stage 5 (Zoom Out), Stage 6 (Comprehension), Stage 8 (Zoom Out), Stage 9 (Codebase Details) | Correct non-existent migration column reference: change `'clinic_hospital_name'` to `'clinic_name'`. |
| **ACT-09** | 🟡 **Major** | Stage 7 (Design Critic), Stage 8 (Zoom Out) | Ensure `logVisit` action generates UUIDs and prevents duplicate same-day visits for offline sync integrity. |
| **ACT-10** | 🟡 **Major** | Stage 7 (Design Critic), Stage 9 (Tech Lead, Codebase Details) | Add request validation and attribute mapping in `SyncController::syncDoctors()`, and denormalize tier metadata. |
| **ACT-11** | 🟡 **Major** | Stage 9 (Codebase Details) | Add `database/factories/DoctorPotentialFactory.php` for Pest testing and model completeness. |
| **ACT-12** | 🟡 **Major** | Stage 5 (Zoom Out), Stage 8 (Zoom Out) | Remove redundant Julian day SQL math and unused `visit_deficit` calculation; sort natively on `last_visit_date ASC NULLS FIRST`. |
| **ACT-13** | 🟡 **Major** | Stage 5 (Zoom Out), Stage 8 (Zoom Out) | DRY form schema: extract schedule components into `DoctorForm::getScheduleSection()` shared with slideover. |
| **ACT-14** | 🟡 **Major** | Stage 9 (Tech Lead) | Provide explicit verification test commands in Phase 6. |
| **ACT-15** | 🟡 **Major** | Stage 7 (Style), Stage 9 (Style) | Strip unmeasured performance hyperbole ("sub-millisecond") and marketing buzzwords ("effortless", "seamless"). |
| **ACT-16** | 🟢 **Nit** | Stage 6 (Comprehension), Stage 9 (Tech Lead) | Include `is_active` in Phase 1 migration columns, `$fillable`, and `$casts`. |
| **ACT-17** | 🟢 **Nit** | Stage 6 (Comprehension), Stage 8 (Zoom Out) | Define acronyms (DCR, OPD, Beat) and update mobile app references to `/elos`. |
| **ACT-18** | 🟢 **Nit** | Stage 9 (Codebase Details) | Eager-load `potential` in `DoctorsTable.php` to avoid N+1 queries. |
| **ACT-19** | 🟢 **Nit** | Stage 7 (Structural) | Escape Mermaid `<` and `>` operators in node labels. |

---

## 3. Consolidated Actionable Remediation Plan

### 🔴 Critical Blockers (Must Be Fixed Before Implementation)

#### 1. ACT-01: Insert Mandatory `## Risks & Mitigations` Section
* **Requirement**: Add an explicit H2 section prior to `## Detailed Implementation Plan` addressing:
  1. *SQLite Write Contention*: Mitigate database locking during concurrent mobile sync and admin console DCR entry by utilizing SQLite WAL mode and wrapping write transactions in retry blocks.
  2. *Timezone & Date Boundary Drift*: Eliminate reliance on SQLite `localtime`; bind `Carbon::today()->toDateString()` in local IST.
  3. *Subquery Scalability*: Benchmark correlated subquery performance and define indexing thresholds on `d_c_r_s(doctor_id, date)`.

#### 2. ACT-02: Query Engine Architecture & SQL Cardinality Fix
* **Requirement**: Replace `fromSub()` with direct `selectSub()` on `Doctor::query()`, and decompose the correlated subquery into 3 discrete scalar queries:
  ```php
  $totalVisitsQuery = DCR::query()
      ->selectRaw('COUNT(*)')
      ->whereColumn('d_c_r_s.doctor_id', 'doctors.id');

  $visitsThisMonthQuery = DCR::query()
      ->selectRaw('COUNT(*)')
      ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
      ->where('d_c_r_s.date', '>=', now()->startOfMonth()->toDateString())
      ->where('d_c_r_s.date', '<=', now()->endOfMonth()->toDateString());

  $lastVisitDateQuery = DCR::query()
      ->selectRaw('MAX(d_c_r_s.date)')
      ->whereColumn('d_c_r_s.doctor_id', 'doctors.id');

  $query = Doctor::query()
      ->where('doctors.status', 'active')
      ->select('doctors.*')
      ->selectSub($totalVisitsQuery, 'total_visits_count')
      ->selectSub($visitsThisMonthQuery, 'visits_this_month_count')
      ->selectSub($lastVisitDateQuery, 'last_visit_date')
      ->with(['potential', 'area.headquarter']);
  ```

#### 3. ACT-03: Partition Tabs Strictly by Commercial Potential Tier
* **Requirement**: Decouple tab membership from visit recency. The default tab must show all non-unresponsive doctors, ensuring all 140 unvisited accounts and severely neglected Core prescribers remain visible:
  ```php
  public function getTabs(): array
  {
      return [
          'active' => Tab::make('Active Cadence')
              ->badge(fn () => Doctor::whereHas('potential', fn ($q) => $q->where('code', '!=', 'unresponsive'))->orWhereNull('doctor_potential_id')->count())
              ->modifyQueryUsing(fn (Builder $query) => $query
                  ->where(function (Builder $q) {
                      $q->whereHas('potential', fn ($p) => $p->where('code', '!=', 'unresponsive'))
                        ->orWhereNull('doctor_potential_id');
                  })),
          'unresponsive' => Tab::make('Unresponsive Accounts')
              ->badge(fn () => Doctor::whereHas('potential', fn ($q) => $q->where('code', 'unresponsive'))->count())
              ->modifyQueryUsing(fn (Builder $query) => $query
                  ->whereHas('potential', fn ($q) => $q->where('code', 'unresponsive'))),
          'all' => Tab::make('All Accounts'),
      ];
  }
  ```

#### 4. ACT-04: Harmonize Potential Tier UI Color Tokens
* **Requirement**: Unify color mapping across architecture tables, seeders, and column badges to a single deterministic standard:
  - **Core**: `success` (Green)
  - **Serious**: `info` (Blue)
  - **Nascent**: `warning` (Amber)
  - **Unresponsive**: `gray` (Neutral Gray)

#### 5. ACT-05: Align Filament v5 Page Architecture & Contracts
* **Requirement**: In `app/Filament/Pages/DoctorCadence.php`, implement `HasTable` using `HasTabs` and `InteractsWithTable`. Compose the layout via `content(Schema $schema): Schema` returning `[$this->getTabsContentComponent(), EmbeddedTable::make()]`, and render `{{ $this->content }}` in `doctor-cadence.blade.php`.

#### 6. ACT-06: Resolve Topological Dependency Inversion in Plan
* **Requirement**: Move `app/Models/DoctorPotential.php` and `app/Models/Doctor.php` creation into Phase 1 prior to executing `DoctorPotentialSeeder.php`.

---

### 🟡 Major Issues

#### 7. ACT-07: Guard Against Empty Array Dehydration in Schedule Filters
* **Requirement**:
  1. In `DoctorForm.php`: Add `dehydrateStateUsing(fn ($state) => empty($state) ? null : array_values($state))` to `visiting_days` and `visiting_shifts`.
  2. In `Doctor.php`: Update scopes `availableOnDay` and `availableOnShift` to check `orWhereNull('visiting_days')->orWhere('visiting_days', '[]')->orWhere('visiting_days', '')`.

#### 8. ACT-08: Correct Schema Column Name to `clinic_name`
* **Requirement**: Change `->after('clinic_hospital_name')` to `->after('clinic_name')` across all migration and model specifications.

#### 9. ACT-09: Guarantee DCR UUID Generation & Prevent Duplicate Calls
* **Requirement**:
  1. Add `creating()` boot hook in `app/Models/DCR.php`: `$dcr->uuid = $dcr->uuid ?? (string) Str::uuid();`.
  2. In `logVisit` modal action: verify whether a DCR record already exists for `(doctor_id, date)` and warn the user.

#### 10. ACT-10: Complete `SyncController` Mobile Synchronization Contracts
* **Requirement**:
  1. In `SyncController::masterData()`: Denormalize tier metadata onto the doctor payload (`code`, `name`, `color`, `target_visits_per_month`).
  2. In `SyncController::syncDoctors()`: Add `$request->validate()` rules for `doctor_potential_id`, `visiting_days`, and `visiting_shifts`, and map them into `Doctor::updateOrCreate()`.

#### 11. ACT-11: Create `DoctorPotentialFactory`
* **Requirement**: Add `database/factories/DoctorPotentialFactory.php` [Create] in Phase 2.

#### 12. ACT-12: Strip Redundant SQL Date Math and Dead Calculations
* **Requirement**: Drop SQLite `julianday(?)` math and `visit_deficit` calculations from the query engine. Sort overdue accounts directly on `last_visit_date ASC NULLS FIRST`, and render relative time via Carbon in PHP.

#### 13. ACT-13: Unify Schedule Form Schema
* **Requirement**: Extract the schedule components into a reusable static method `DoctorForm::getScheduleComponents()` and invoke it in both `DoctorForm` and the `editSchedule` action slideover.

#### 14. ACT-14: Provide Explicit Verification Commands
* **Requirement**: Add exact test execution instructions:
  ```bash
  php artisan test --compact tests/Feature/DoctorCadenceTest.php
  php artisan test --compact
  ```

#### 15. ACT-15: Apply Technical Copy Edits
* **Requirement**: Replace hyperbolic and promotional phrases ("sub-millisecond", "effortless", "seamless", "zero-friction", "flawless", "100% feature reliability") with dry, objective engineering descriptions.

---

### 🟢 Nits & Structural Refinements

* **ACT-16**: Include `is_active` in Phase 1 migration columns, `$fillable`, and `$casts`.
* **ACT-17**: Spell out acronyms upon first appearance (Daily Call Report - DCR, Outpatient Department - OPD, Beat) and update mobile app references to `/elos`.
* **ACT-18**: Add `modifyQueryUsing(fn ($query) => $query->with('potential'))` in `DoctorsTable.php` to prevent N+1 queries.
* **ACT-19**: Escape Mermaid `<` and `>` operators using `&le;` and `&gt;`.

---

## 4. Next Steps & Recommended Action

Per the EGM specification lifecycle, this review concludes with findings requiring resolution.
You can execute automated remediation via:

```bash
/egm-resolve docs/designs/doctor_cadence.md
```

This will apply all approved fixes from this report directly into [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md), bringing the specification into full compliance and preparing it for implementation.
