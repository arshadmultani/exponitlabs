# Stage 9 Review Report: Implementation Readiness Test

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** Detailed Implementation Plan (Lines 310–453)
* **Target Standard:** [`implementation-readiness-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/implementation-readiness-standard.md)
* **Reviewers:** Pragmatic Tech Lead (`egm-implementation-readiness`), Codebase Details Auditor (`egm-codebase-details`), Technical Copy Editor (`egm-style`)
* **Review Date:** 2026-11-02
* **Overall Verdict:** FAIL

---

## 1. Executive Summary

Stage 9 evaluated the Detailed Implementation Plan to determine if an implementing engineer could build the feature without guessing, asking questions, or hitting fatal compile/runtime errors.
The evaluation revealed three Blocker defects:
1. **Correlated Subquery SQL Cardinality Violation**: The subquery design attempts to select 3 columns in a single scalar subquery, triggering a fatal SQL exception (`1241 Operand should contain 1 column(s)`).
2. **Broken Filament v5 Page Contracts & Blade View**: Specifying `HasTabs` and rendering `{{ $this->table }}` in Blade violates Filament v5 conventions and completely prevents header tabs from rendering.
3. **Topological Dependency Inversion**: `DoctorPotentialSeeder` calls `DoctorPotential::firstOrCreate()`, but the model is not created until Phase 2, causing immediate class-not-found crashes on Phase 1 execution.

Additionally, multiple Major defects were identified regarding empty JSON array dehydration, missing `SyncController` validation and payload mapping, non-existent database columns (`clinic_hospital_name`), missing model factories, and contradictory color tokens.

---

## 2. Findings by Review Vector

### Vector A: Codebase Architecture & Alignment (Codebase Details Auditor)

#### Finding 9.A.1: Correlated Subquery SQL Cardinality Violation
* **Severity:** Blocker
* **Location:** Line 370 (`app/Services/DoctorCadenceQueryService.php`)
* **Codebase Mismatch:** Line 370 specifies a correlated subquery on `d_c_r_s` calculating `COUNT(d_c_r_s.id) AS total_visits_count`, `MAX(d_c_r_s.date) AS last_visit_date`, and `COUNT(...) AS visits_this_month_count` simultaneously. In SQL and Eloquent `selectSub()`, scalar subqueries must return exactly 1 column. Returning 3 columns triggers `SQLSTATE[21000]: Cardinality violation: 1241 Operand should contain 1 column(s)`.
* **Required Code Fix:** Attach each subquery as an independent scalar query:
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
      ->select('doctors.*')
      ->selectSub($totalVisitsQuery, 'total_visits_count')
      ->selectSub($visitsThisMonthQuery, 'visits_this_month_count')
      ->selectSub($lastVisitDateQuery, 'last_visit_date');
  ```

#### Finding 9.A.2: Filament v5 Page Schema Contract & Blade View Broken for Tabs
* **Severity:** Blocker
* **Location:** Line 400 & Line 426 (`app/Filament/Pages/DoctorCadence.php` & `doctor-cadence.blade.php`)
* **Codebase Mismatch:** Line 426 specifies the blade template content as `<x-filament-panels::page> {{ $this->table }} </x-filament-panels::page>`. In Filament v5 (as established in `DoctorPortfolio.php` and `doctor-portfolio.blade.php`), pages implementing `HasTabs` must define `content(Schema $schema): Schema` returning `[$this->getTabsContentComponent(), EmbeddedTable::make()]`, and render `{{ $this->content }}` in Blade. Rendering `{{ $this->table }}` bypasses page schemas and fails to render tabs.
* **Required Code Fix:**
  In `doctor-cadence.blade.php`:
  ```blade
  <x-filament-panels::page>
      {{ $this->content }}
  </x-filament-panels::page>
  ```
  In `DoctorCadence.php`:
  ```php
  use Filament\Pages\Page;
  use Filament\Resources\Concerns\HasTabs;
  use Filament\Schemas\Components\EmbeddedTable;
  use Filament\Schemas\Schema;
  use Filament\Tables\Concerns\InteractsWithTable;
  use Filament\Tables\Contracts\HasTable;

  class DoctorCadence extends Page implements HasTable
  {
      use HasTabs;
      use InteractsWithTable;

      public function content(Schema $schema): Schema
      {
          return $schema->components([
              $this->getTabsContentComponent(),
              EmbeddedTable::make(),
          ]);
      }
  }
  ```

#### Finding 9.A.3: Silent Filter Eviction on CheckboxList Empty Array Dehydration
* **Severity:** Major
* **Location:** Lines 331, 362–363, 385–386, 420–421
* **Codebase Mismatch:** Clearing all checkboxes in a Filament `CheckboxList` dehydrates to `[]` (stored as `'[]'` string in SQLite), rather than `NULL`. Any doctor saved with an empty schedule fails `whereNull('visiting_days')` and is excluded from day and shift route filters.
* **Required Code Fix:** Add `dehydrateStateUsing(fn ($state) => empty($state) ? null : array_values($state))` on form fields, and add `orWhere('visiting_days', '[]')` to model query scopes.

#### Finding 9.A.4: Incomplete `SyncController` Validation & Payload Mapping
* **Severity:** Major
* **Location:** Line 436 (`app/Http/Controllers/Api/SyncController.php`)
* **Codebase Mismatch:** `SyncController::syncDoctors()` enforces strict `$request->validate()` rules and uses a hardcoded array in `Doctor::updateOrCreate()`. Omitting validation and mapping rules causes incoming mobile schedule/tier data to fail validation or be silently dropped.
* **Required Code Fix:** Add validation rules and payload mapping for `doctor_potential_id`, `visiting_days`, and `visiting_shifts`.

#### Finding 9.A.5: Non-Existent Column Reference in Migration
* **Severity:** Major
* **Location:** Line 160 / Line 327
* **Codebase Mismatch:** Migration specifies `->after('clinic_hospital_name')`. The actual column on `doctors` is `clinic_name`.
* **Required Code Fix:** Change to `->after('clinic_name')`.

#### Finding 9.A.6: Missing Model Factory for `DoctorPotential`
* **Severity:** Major
* **Location:** Line 349 & Line 443
* **Codebase Mismatch:** Creating a new model without a factory violates project guidelines (`AGENTS.md`) and breaks `RefreshDatabase` Pest test isolation.
* **Required Code Fix:** Add `database/factories/DoctorPotentialFactory.php` [Create] to Phase 2.

---

### Vector B: Pragmatic Execution & Contracts (Tech Lead)

#### Finding 9.B.1: Topological Dependency Inversion (Seeder vs. Model)
* **Severity:** Blocker
* **Location:** Phase 1 (Item 3, Lines 335–344) & Phase 2 (Item 4, Lines 349–355)
* **Readiness Defect:** Phase 1 item 3 executes `DoctorPotential::firstOrCreate(...)`, but `DoctorPotential.php` is scheduled for Phase 2. Running migrations and seeders in Phase 1 fails with `Class "App\Models\DoctorPotential" not found`.
* **Recommended Remediation:** Move `DoctorPotential.php` and `Doctor.php` relationship updates into Phase 1 before running the seeder.

#### Finding 9.B.2: Critical Contradiction in Commercial Tier Color Tokens
* **Severity:** Blocker
* **Location:** Architecture (Lines 144–150) vs Seeder (Lines 338–341)
* **Readiness Defect:** Core is defined as `success` (Green) in architecture, but `'color' => 'danger'` (Red) in the seeder.
* **Recommended Remediation:** Unify to Core: `success`, Serious: `info`, Nascent: `warning`, Unresponsive: `danger` (or `gray`).

#### Finding 9.B.3: Missing Automated Test Execution Commands
* **Severity:** Major
* **Location:** Phase 6 (Lines 440–453)
* **Readiness Defect:** Omits the exact test commands required by engineers and CI.
* **Recommended Remediation:** Include explicit commands:
  ```bash
  php artisan test --compact tests/Feature/DoctorCadenceTest.php
  php artisan test --compact
  ```

---

### Vector C: Technical Style & Precision (Style Editor)

#### Finding 9.C.1: Promotional Fluff & Absolute Claims
* **Severity:** Major
* **Location:** Lines 312, 433, 443
* **Defect:** "seamless database migration", "zero regression", "flawless synchronization", "enforcing 100% feature reliability".
* **Replacement:** Replace with objective, measurable technical specifications.
