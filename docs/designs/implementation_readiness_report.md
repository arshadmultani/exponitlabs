# Stage 9: Implementation Readiness Report

**Target File:** `docs/designs/doctor_portfolio.md`  
**Target Scope:** Detailed Implementation Plan (Lines 324–418)  
**Standard:** `resources/document_standards/implementation-readiness-standard.md`  
**Reviewers:** `egm-implementation-readiness` (Pragmatic Tech Lead), `egm-codebase-details` (Codebase Details Auditor), `egm-style` (Technical Copy Editor)  
**Date:** 2026-11-01  
**Overall Verdict:** **FAIL**

---

## Executive Summary
The Detailed Implementation Plan fails the Implementation Readiness Standard and the Zero-Decision Rule. A software engineer or automated agent attempting to implement this plan directly will encounter immediate blocker database exceptions at runtime:
1. `DoctorPortfolioQueryService` does not formulate `total_investment` or `net_pnl` in SQL, causing `->defaultSort('net_pnl', 'desc')` to crash on SQLite.
2. The plan creates schema duplication by adding `products.sample_cost` without recognizing that `products.unit_cost` already exists in the database and Filament UI.
3. It completely omits CSV file schemas, service signatures, and error handling for `DoctorRevenueImportService`.
4. It lacks quantitative thresholds for Prescriber Performance Tiers.
5. Scope files lack explicit `[Create]`, `[Modify]`, and `[Delete]` action modifiers.

---

## Detailed Findings by Reviewer Vector

### A. Pragmatic Tech Lead Vector (`egm-implementation-readiness`)

#### 1. [Blocker] Missing SQL Formulation for Sortable `net_pnl` and `total_investment`
* **Affected Scope:** Section 3 (Lines 358–363) & Section 4 (Lines 205–254) (`DoctorPortfolioQueryService.php`)
* **Finding & Guidance:** 
  The plan asserts that `DoctorPortfolioQueryService` builds correlated subqueries for `visits_count`, `samples_cost`, `promos_cost`, `total_revenue`, and `net_pnl`, and that the Filament prescriber ledger natively sorts by `net_pnl DESC` and `total_investment`.
  However, the provided reference implementation in Section 4 (`getBaseQuery()`) ends after selecting `total_revenue`. It does not calculate `total_investment` or `net_pnl`. In SQL and SQLite, alias columns defined within the same `SELECT` clause cannot be referenced directly in sibling `SELECT` expressions. 
  Because the query does not formulate `net_pnl`, the implementer is forced to invent the query architecture—deciding whether to duplicate subqueries inside a composite `selectRaw`, wrap the query in an outer CTE/derived table, or configure custom Filament table sort closures.
  *Guidance*: The plan must provide the complete SQL select expression or query builder implementation for `total_investment` and `net_pnl`, explicitly detailing how doctor visit cost override fallback logic (`COALESCE(doctors.visit_cost_override, 250.00) * visits_count`) is calculated in SQL.

#### 2. [Blocker] Missing Quantitative Criteria & Thresholds for Prescriber Performance Tiers
* **Affected Scope:** Section 4 (Lines 377–380) & Architecture (Line 284) (`DoctorPortfolio.php`)
* **Finding & Guidance:** 
  The table specification requires a `tier` badge column categorizing doctors into `High-Yield Prescriber` (Success), `Balanced` (Warning), `Under-Yielding` (Danger), or `Incubation` (Info if registered < 60 days).
  Nowhere in the document are the actual mathematical rules or threshold ranges specified. Does "High-Yield" require ROI > 20%, ROI > 50%, or simply Net P&L > ₹0? What defines "Balanced"? What defines "Under-Yielding"? For "Incubation", does it check `doctors.created_at` or `doctors.practice_since`, and does it take precedence over other tiers if revenue is 0?
  Without concrete rules, the implementer cannot write the badge logic without inventing arbitrary business thresholds.
  *Guidance*: Document the exact `match` / conditional evaluation logic, precedence order, and numeric threshold boundaries for each tier.

#### 3. [Blocker] Undefined CSV Schema, Service Contract, and Period Date Contradiction
* **Affected Scope:** Section 3 (Lines 364–366) & Section 4 (Lines 381–382) (`DoctorRevenueImportService.php`)
* **Finding & Guidance:** 
  The plan instructs the engineer to build `DoctorRevenueImportService` to ingest monthly revenue spreadsheets, but omits essential technical contracts:
  1. **CSV Header Contract**: The expected CSV headers are nowhere defined (e.g., `doctor_id,period_date,amount,notes` vs `doctor_id,month,year,amount`).
  2. **Architectural Contradiction on Period Date**: CUJ 2 (Line 72) specifies that the user "selects the October 2026 period, and uploads a validated CSV file" in the modal. Conversely, Section 1 (Line 335) and Section 3 (Line 365) state that the service upserts records using `period_date` from the import file. It is completely ambiguous whether `period_date` is a column in the CSV, a modal form input, or both.
  3. **Service Signature & Return Structure**: The class signature is omitted. The implementer does not know whether `import()` accepts an `UploadedFile`, a file path, or a stream, nor what data structure constitutes "detailed success/error feedback" (e.g., a custom DTO vs. an array reporting imported, updated, and failed rows).
  4. **Transaction Strategy**: The plan does not specify if the import is atomic (all-or-nothing rollback on any validation failure) or row-tolerant (valid rows committed while collecting error rows).
  *Guidance*: Define the required CSV column headers and date formats, resolve whether `period_date` is sourced from modal input or CSV rows, document the explicit method signature and return type, and specify the batch transaction/error strategy.

#### 4. [Major] Contradictory Stats Card Definitions & Missing Page-to-Widget Filter Sync
* **Affected Scope:** Section 4 (Lines 382–384) & Architecture (Lines 269–273) (`DoctorPortfolioStatsWidget.php`)
* **Finding & Guidance:** 
  1. **Card Definition Contradiction**: Lines 272–273 define Card 3 as "Net Commercial P&L (with return multiple)" and Card 4 as "Account Efficiency Ratio (% of active doctors generating net-positive margin)". Line 383 contradicts this, specifying Card 3 as "Net Commercial P&L" and Card 4 as "Overall Return Multiple". If Account Efficiency Ratio is intended, "active doctor" is never defined.
  2. **Filter State Synchronization**: The page contains time-horizon tab filters (`All Time`, `Current FY`, `90 Days`, `Last Month`), but the plan does not define how the widget receives the active horizon. In Filament v5 / Livewire, widgets rendered via `getHeaderWidgets()` do not automatically receive page tab selections without explicit parameter passing (`$widgetData`), Livewire reactive properties, or dispatched events.
  *Guidance*: Resolve the Card 4 definition and provide formulas for all 4 cards (including zero-division handling for return multiple). Explicitly specify the Livewire/Filament communication mechanism between `DoctorPortfolio` and `DoctorPortfolioStatsWidget`.

#### 5. [Major] Phantom Class Under Test in Unit Test Plan
* **Affected Scope:** Section 6 (Lines 402–407) (`tests/Unit/DoctorFinancialMetricsTest.php`)
* **Finding & Guidance:** 
  The plan specifies `tests/Unit/DoctorFinancialMetricsTest.php` to validate visit cost resolution, sample/promotional input multiplication, zero-division guards, and financial year date filtering.
  However, no `DoctorFinancialMetrics` class exists or is scheduled for creation in Section 2 or 3. All calculations are designated to live in SQL subqueries inside `DoctorPortfolioQueryService`. Unit tests in `tests/Unit` do not boot Laravel's database or Eloquent subquery engine. An implementer attempting to write this test has no target PHP unit to test against.
  *Guidance*: Either specify a dedicated domain calculation service/DTO (e.g., `App\Support\DoctorFinancialMetrics`), or relocate these test cases to a database-backed feature test suite: `tests/Feature/Services/DoctorPortfolioQueryServiceTest.php` extending `TestCase` with `RefreshDatabase`.

#### 6. [Major] Missing Modal Schema for "Log Monthly Revenue"
* **Affected Scope:** Section 4 (Lines 377–381) (`DoctorPortfolio.php`)
* **Finding & Guidance:** 
  The plan requires a "Log Monthly Revenue" header action on the page, but gives no schema definition. The implementer must guess which fields are included, validation rules (e.g., preventing future dates or negative amounts), and how `period_date` is normalized to the first day of the month before persisting.
  *Guidance*: Provide the explicit Filament `Action::make('logRevenue')->schema([...])` definition, validation constraints, and submit action handler.

#### 7. [Nit] Unspecified Indian Financial Year Date Range Calculation
* **Affected Scope:** Section 3 (Lines 358–363) (`DoctorPortfolioQueryService.php`)
* **Finding & Guidance:** 
  The date horizon `This Financial Year (India Apr 1 – Mar 31)` requires dynamic date boundary logic depending on whether the current month is before or after April. The plan lacks the Carbon helper contract defining this range.
  *Guidance*: Specify a helper method contract (e.g., `resolveHorizonRange(string $horizon): array<Carbon>`) detailing the start and end dates for all 4 horizons.

#### 8. [Nit] Missing Phase Checkpoints & Intermediate Verification Commands
* **Affected Scope:** Implementation Plan Structure (Lines 324–418)
* **Finding & Guidance:** 
  The plan groups tasks into 6 phases but places all verification at the very end in Phase 6. There are no intermediate execution commands (e.g., `php artisan migrate`, `php artisan db:seed`, intermediate test runs) to confirm that Phases 1 through 3 leave the codebase in a stable, buildable, and testable state.
  *Guidance*: Add intermediate CLI verification checkpoints at the conclusion of each phase.

---

### B. Codebase Details Vector (`egm-codebase-details`)

#### 1. [Blocker] Redundant Schema Drift — `products.sample_cost` vs Existing `products.unit_cost`
* **Affected Scope:** Lines 330–331, 347–348, 373–374 (`Product.php`, `ProductForm.php`)
* **Discrepancy Description & Rationale:**
  Migration 1 (line 330) and Section 2 (line 347) specify adding a new column `sample_cost` (`decimal:2`) to the `products` table and model. Section 4 (line 373) states: *"Expose `sample_cost` in the product management form and table."*
  In the actual codebase, `products` **already has** a `unit_cost` column (`decimal:2`, added in migration `2026_06_24_100001_add_pricing_fields_to_products_table.php`). It is already listed in `Product::$fillable`, cast as `decimal:2`, and actively exposed in `ProductForm.php`:
  ```php
  TextInput::make('unit_cost')
      ->label('Unit cost (₹)')
      ->numeric()
      ->helperText('Company cost per unit (incl. GST). Optional.')
  ```
  The plan does not acknowledge that `products.unit_cost` exists. It creates schema duplication and confusion over whether a product has two distinct costs (`unit_cost` and `sample_cost`), or whether `sample_cost` was proposed because the author was unaware that `unit_cost` was already in the database and UI.

#### 2. [Major] Scope Files Lack Action Modifiers (`[Create]`, `[Modify]`, `[Delete]`)
* **Affected Scope:** Lines 328–418 (Sections 1 through 6)
* **Discrepancy Description & Rationale:**
  The Implementation Readiness Standard and Goldfish mandate require explicit labeling of each file with `[Create]`, `[Modify]`, or `[Delete]`. Every entry in lines 328–418 is formatted as a bare filepath with a rationale bullet point. A fresh engineer or AI session cannot immediately discern which files exist currently (such as `PromotionalInputResource.php` and `DoctorForm.php`) versus those that must be newly created (e.g. `config/exponit.php`, `DoctorRevenue.php`).

#### 3. [Major] Missing Factory Specification for `DoctorRevenue`
* **Affected Scope:** Lines 351–352, 408–418
* **Discrepancy Description & Rationale:**
  The plan introduces `DoctorRevenue` and specifies extensive automated Pest tests (`DoctorPortfolioTest.php`, `DoctorRevenueImportTest.php`), but omits `database/factories/DoctorRevenueFactory.php`. Project rules explicitly state: *"When creating new models, create useful factories and seeders for them too."* and *"When creating models for tests, use the factories for the models."* Without `DoctorRevenueFactory`, tests will resort to manual database inserts rather than idiomatic Pest/Laravel factories.

#### 4. [Nit] Already Completed Task in Sitemap
* **Affected Scope:** Lines 395–397 (`docs/designs/SITEMAP.md`)
* **Discrepancy Description & Rationale:**
  Line 395 specifies registering `docs/designs/doctor_portfolio.md` in `docs/designs/SITEMAP.md`. Inspection of `docs/designs/SITEMAP.md#L25` shows it is already registered. This step is already complete.

---

### C. Style Vector (`egm-style`)

#### 1. [Major] Contradictory "Minor-Unit Monetary Formatting Helpers" on Decimal Column
* **Affected Scope:** Section 2 (`app/Models/DoctorRevenue.php`), Line 352
* **Style Rationale:** The phrase *"minor-unit monetary formatting helpers"* introduces technical contradiction and ambiguity. The database migration specification in Section 2 defines `amount` as `decimal(12, 2)`, not an integer minor unit (e.g., cents/paise). Describing it as "minor-unit" conflicts with the data model. Furthermore, "formatting helpers" is vague; if accessor methods or casts are required, name them concretely.

#### 2. [Major] Inaccurate Runtime Error Terminology in Unit Test Specification
* **Affected Scope:** Section 6 (`tests/Unit/DoctorFinancialMetricsTest.php`), Line 406
* **Style Rationale:** *"Zero-division guard: ensure doctors with ₹0 investment and positive revenue evaluate cleanly without arithmetic overflow."* In PHP 8, division by zero raises a `DivisionByZeroError`, not an "arithmetic overflow". Additionally, "evaluate cleanly" is subjective and non-actionable. State explicitly how the condition must resolve (e.g., returns `0.0` or `null`).

#### 3. [Nit] Marketing Jargon and Imprecise Descriptors
* **Affected Scope:** Lines 326, 331, 344, 359, 372, 377, 380, 383, 408, 412, 416, 418
* **Style Rationale:** Streamline inflated phrases ("concrete cost baselines", "global fallback visit expenditure"), remove filler adverbs ("accurately", "successfully"), and eliminate repetitive marketing descriptors ("executive portfolio page", "high-performance", "standard").
