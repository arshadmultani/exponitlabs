# Consolidated Goldfish Review Report

**Target Document:** [`docs/designs/doctor_portfolio.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_portfolio.md)  
**Evaluated Scope:** Full Document (Lines 1–418)  
**Semantic Boundary:** `FeatureRange`: lines 1–322 | `DetailedRange`: lines 324–418  
**Pipeline Preset:** Consolidated Report (Uninterrupted Full Flow across Stages 5–9)  
**Date:** 2026-11-01  
**Consolidated Verdict:** **FAIL** (Actionable remediation required across 5 Blockers and 8 Major findings prior to implementation).

---

## Executive Summary

The proposed "Doctor Commercial Performance & ROI Tracker" addresses a genuine commercial problem for Exponit Labs: establishing financial visibility over field detailing investments, sampling costs, and promotional expenditures across 233 physicians. The design commendably decouples the Laravel application from premature ERPNext schema dependencies and avoids speculative caching infrastructure (materialized roll-up tables, Redis queues).

However, across all five zero-context Goldfish review stages (Initial Zoom Out, Naive Reader Comprehension, Adversarial Critic Swarm, Intermediate Zoom Out, and Implementation Readiness), independent reviewers unanimously identified critical architectural, mathematical, and structural defects that would cause runtime PDO database crashes, historical accounting corruption, and implementation stalls.

This consolidated report synthesizes all raw findings from the 5 stage reports into deduplicated, prioritized action items supported by a full Traceability Matrix.

---

## Consolidated Action Items by Priority

### 🔴 Blockers (Must Resolve Before Implementation Begins)

#### [TASK-B1] Formulate Sortable SQL Projections for `net_pnl` and `total_investment` via `fromSub()`
* **Primary Defect:** The specification requires native SQLite sorting on `net_pnl DESC` (`defaultSort`) and `total_investment`. However, the query service reference implementation only selects `visits_count`, `samples_cost`, `promos_cost`, and `total_revenue`. In SQLite and standard SQL, aliases defined in the `SELECT` clause cannot be referenced in sibling select expressions at the same projection level. Attempting to sort by `net_pnl` crashes immediately at runtime with `SQLSTATE[HY000]: General error: 1 no such column: net_pnl`.
* **Actionable Fix:** Update `DoctorPortfolioQueryService::getBaseQuery()` to use a derived query via Eloquent's `fromSub()`:
  ```php
  $innerQuery = Doctor::query()
      ->select('doctors.*')
      ->selectSub($visitsCountQuery, 'visits_count')
      ->selectSub($samplesCostQuery, 'samples_cost')
      ->selectSub($promosCostQuery, 'promos_cost')
      ->selectSub($totalRevenueQuery, 'total_revenue');

  return Doctor::query()
      ->fromSub($innerQuery, 'doctors')
      ->select('doctors.*')
      ->selectRaw('((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost) AS total_investment', [$defaultVisitCost])
      ->selectRaw('(doctors.total_revenue - ((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost)) AS net_pnl', [$defaultVisitCost]);
  ```
  This creates real SQL columns `total_investment` and `net_pnl` that Filament's native sorting can sort on in SQLite with zero migration views.

#### [TASK-B2] Insert Mandatory "Risks & Mitigations" Section into Feature Specification
* **Primary Defect:** The Feature Specification Standard strictly mandates an evaluation of constraints, failure modes, security, and scalability limits. Lines 1–322 completely omit this section.
* **Actionable Fix:** Insert a dedicated `## Risks & Mitigations` section addressing:
  1. *SQLite Write Concurrency:* Single-writer locking during CSV imports, mitigated by sub-20ms atomic transactions and WAL mode.
  2. *Healthcare Regulatory & Privacy Compliance (UCPMP):* Restricting prescriber commercial profitability data to authorized management roles.
  3. *Historical Accounting Mutability:* Guarding closed financial periods against master catalog price changes.
  4. *Date Horizon Normalization:* Aligning monthly revenue audits with daily DCR logs.

#### [TASK-B3] Define Deterministic Quantitative Boundaries & Precedence for Prescriber Tiers
* **Primary Defect:** Prescriber badge tiers (`High-Yield`, `Balanced`, `Under-Yielding`, `Incubation`) rely on subjective qualitative language (*"significantly exceeds"*, *"cross threshold limits"*) without numerical cutoffs.
* **Actionable Fix:** Document the exact evaluation logic, precedence order, and threshold cutoffs in Section 3 and Section 5:
  1. **Incubation:** Evaluated first: `doctors.created_at >= now()->subDays(60)` (Badge: Info / Gray).
  2. **High-Yield Prescriber:** `net_pnl > 0` AND `roi_percentage >= 50.0` (Badge: Success / Green).
  3. **Balanced Prescriber:** `net_pnl >= 0` AND `roi_percentage < 50.0` (Badge: Warning / Amber).
  4. **Under-Yielding Prescriber:** `net_pnl < 0` (Badge: Danger / Red).

#### [TASK-B4] Define Technical Ingestion Contract for `DoctorRevenueImportService`
* **Primary Defect:** The implementation plan requires `DoctorRevenueImportService` but omits the CSV column format, method signature, date resolution, and error semantics.
* **Actionable Fix:** Document the complete technical contract in Section 3 and Section 4:
  - **CSV Column Schema:** `doctor_id,period_date,amount,notes` (with fallback supporting modal period selection).
  - **Date Format:** Normalized to `YYYY-MM-01`.
  - **Service Method Signature:**
    ```php
    public function import(UploadedFile $file, ?Carbon $fallbackPeriod = null): RevenueImportResult
    ```
  - **Transaction & Error Strategy:** Memory validation first, rejecting non-existent `doctor_id`s with row-level error arrays; valid records committed in a single `DB::transaction()` via `DoctorRevenue::upsert()`.

#### [TASK-B5] Eliminate Redundant Schema Drift by Reusing Existing `products.unit_cost`
* **Primary Defect:** The plan proposes a new `sample_cost` column on `products`. In the actual codebase, `products` already has a populated `unit_cost` column (`decimal:2`) exposed in `ProductForm.php`. Introducing `sample_cost` defaulting to `0.00` ignores existing pricing data and causes all existing 233 doctors to evaluate to ₹0 sample investment.
* **Actionable Fix:** Update Migration 1, model casting, and query calculations to reuse `unit_cost`:
  ```php
  COALESCE(products.sample_cost, products.unit_cost, 0.00)
  ```
  Or drop `sample_cost` entirely if pharmaceutical samples share unit cost economics with finished goods.

---

### 🟡 Major Findings (Architectural & Behavioral Corrections)

#### [TASK-M1] Align Date Normalization for Horizon Filters (`last_3_months` / `startOfMonth()`)
* **Defect:** Rolling intra-month date filters (such as `trailing_90` resolving to Aug 3) exclude August monthly revenue stored as `2026-08-01` (`2026-08-01 >= 2026-08-03` is false), while including 28 days of August DCR expenses. This distorts doctor P&L.
* **Fix:** Replace `trailing_90` with calendar-aligned **`last_3_months`**, or explicitly clamp `$startDate` for monthly revenue queries to `$startDate->copy()->startOfMonth()`.

#### [TASK-M2] Resolve Historical Price Mutability via Nullable `unit_cost_snapshot`
* **Defect:** CUJ 3 claims past activities remain unaffected when prices change. However, live joins to catalog tables retroactively alter all closed accounting periods when prices are updated.
* **Fix:** Add a nullable `unit_cost_snapshot` (`decimal:10,2`) column to `dcr_products` and `dcr_promotional_inputs`. Populate it during DCR submission, and update queries to evaluate `COALESCE(dcr_products.unit_cost_snapshot, products.unit_cost, 0.00)`.

#### [TASK-M3] Permit Multi-Chemist Revenue Attribution by Dropping Composite Unique Constraint
* **Defect:** Migration 4 enforces a database-level unique constraint on `(doctor_id, period_date)`. In real-world clinics, prescription revenue audits arrive from multiple chemists within the same month for a single doctor. The unique constraint causes collisions or data overwrites.
* **Fix:** Remove `$table->unique(['doctor_id', 'period_date'])`, replacing it with an indexed `$table->index(['doctor_id', 'period_date'])`. The query service already aggregates with `SUM(amount)`.

#### [TASK-M4] Define State Synchronization Between `DoctorPortfolio` Page and `DoctorPortfolioStatsWidget`
* **Defect:** Filament page header widgets do not automatically inherit tab filter selections from parent pages without explicit wiring. Furthermore, Card 4 definition is contradictory between sections ("Account Efficiency Ratio" vs. "Overall Return Multiple").
* **Fix:**
  - Standardize Card 4 as **"Overall Return Multiple"** (`Total Attributed Revenue / Total Invested Capital`, returning `0.0x` if investment is ₹0).
  - Document the state-passing contract: `DoctorPortfolio` passes the active horizon to `DoctorPortfolioStatsWidget` using `$widgetData = ['horizon' => $this->activeHorizon]`.

#### [TASK-M5] Relocate Unit Test Suite to Database Feature Test Suite
* **Defect:** The test plan specifies `tests/Unit/DoctorFinancialMetricsTest.php`, but no `DoctorFinancialMetrics` class exists. All calculations live in SQL query builders. Unit tests cannot boot database query engines.
* **Fix:** Retitle and relocate to `tests/Feature/Services/DoctorPortfolioQueryServiceTest.php` extending `TestCase` with `RefreshDatabase`. Add `database/factories/DoctorRevenueFactory.php`.

#### [TASK-M6] Add Action Modifiers (`[Create]`, `[Modify]`, `[Delete]`) to All Scope Files
* **Defect:** Scope files in lines 328–418 are listed as bare paths without action markers, violating the Implementation Readiness Standard.
* **Fix:** Prefix each entry in Section 1–6 with `[Create]`, `[Modify]`, or `[Delete]`.

#### [TASK-M7] Provide Explicit Modal Form Schema for "Log Monthly Revenue"
* **Defect:** The plan specifies a "Log Monthly Revenue" header action on the page but provides no form schema or validation rules.
* **Fix:** Document the Filament action modal schema: `doctor_id` (Select, required, searchable), `period_date` (DatePicker, format Y-m-d, default start of current month, required), `amount` (TextInput, numeric, min 0.01, required), `notes` (TextInput, nullable).

#### [TASK-M8] Eliminate Conflicting "Minor-Unit" Terminology and Runtime Error Descriptions
* **Defect:** `DoctorRevenue` is described as having "minor-unit formatting helpers" while the database column is `decimal(12, 2)`. Line 406 references "arithmetic overflow" instead of PHP 8's `DivisionByZeroError`.
* **Fix:** Standardize terminology to standard decimal rupee formatting and `DivisionByZeroError`.

---

### 🟢 Nits (Code Quality, Formatting, and Style Cleanliness)

* **[TASK-N1] Align Blueprint Section Headings:** Canonicalize `## The Problem` to `## Problem Statement`, unify `Goals & Non-Goals`, and rename `## Technical Plan (Architecture)` to `## Proposed Architecture`.
* **[TASK-N2] Remove Duplicate Composite Index:** In Migration 4, drop `$table->index(['doctor_id', 'period_date'])` if unique constraint is kept, or eliminate redundant duplicate index definitions.
* **[TASK-N3] Strip Hyperbole, Slang, and Promotional Fluff:** Replace emotional rhetoric ("severe cross-stack coupling", "N+1 query explosion", "YAGNI", "high-performance", "executive") with dry, neutral engineering prose.
* **[TASK-N4] Fix LaTeX Math Formatting:** Clean up unescaped double question marks and quotes in inline math expressions on Line 178.
* **[TASK-N5] Link File Paths, Symbols, and External Concepts:** Add markdown links to referenced models, console routes, and explain domain acronyms (`UCPMP`, `ERPNext`).
* **[TASK-N6] Add Intermediate CLI Verification Checkpoints:** Insert execution checkpoints (`php artisan migrate`, `php artisan test`) at the end of each implementation phase.
* **[TASK-N7] Reconcile Sitemap Task:** Mark `docs/designs/SITEMAP.md` update as already completed.

---

## Traceability Matrix

| Raw Finding Source | Reviewer & Stage | Category | Consolidated Action Item |
| :--- | :--- | :--- | :--- |
| Query Service Omits Sortable Columns | `egm-design` (Stage 7.A2), `egm-zoom-out` (Stage 5.1), `egm-comprehension` (Stage 6.1), `egm-implementation-readiness` (Stage 9.A1), `egm-codebase-details` (Stage 9.B1) | 🔴 Blocker | **[TASK-B1]** |
| Missing "Risks & Mitigations" Section | `egm-structural` (Stage 7.B1), `egm-design` (Stage 7.A1) | 🔴 Blocker | **[TASK-B2]** |
| Undefined Prescriber Tier Thresholds | `egm-style` (Stage 7.C1), `egm-design` (Stage 7.A5), `egm-comprehension` (Stage 6.3), `egm-implementation-readiness` (Stage 9.A2) | 🔴 Blocker | **[TASK-B3]** |
| Missing CSV Ingestion Schema & Contract | `egm-comprehension` (Stage 6.2), `egm-implementation-readiness` (Stage 9.A3), `egm-codebase-details` (Stage 9.B5) | 🔴 Blocker | **[TASK-B4]** |
| Schema Drift: `products.sample_cost` vs `unit_cost` | `egm-codebase-details` (Stage 9.B2), `egm-zoom-out` (Stage 5.5, Stage 8.6) | 🔴 Blocker | **[TASK-B5]** |
| Date Boundary Mismatch on Rolling Filters | `egm-zoom-out` (Stage 5.2, Stage 8.4), `egm-design` (Stage 7.A4), `egm-comprehension` (Stage 6.5), `egm-codebase-details` (Stage 9.B4) | 🟡 Major | **[TASK-M1]** |
| Historical Pricing Mutability Contradiction | `egm-design` (Stage 7.A3), `egm-zoom-out` (Stage 5.3, Stage 8.3), `egm-comprehension` (Stage 6.7) | 🟡 Major | **[TASK-M2]** |
| Multi-Chemist Sales Attribution Collision | `egm-zoom-out` (Stage 5.4, Stage 8.5) | 🟡 Major | **[TASK-M3]** |
| Page-to-Widget State Sync & Card Contradiction | `egm-comprehension` (Stage 6.4, 6.6), `egm-implementation-readiness` (Stage 9.A4), `egm-codebase-details` (Stage 9.B7, 9.B8) | 🟡 Major | **[TASK-M4]** |
| Phantom Unit Test Class & Missing Factory | `egm-implementation-readiness` (Stage 9.A5), `egm-codebase-details` (Stage 9.B6) | 🟡 Major | **[TASK-M5]** |
| Scope Files Lack Action Modifiers | `egm-codebase-details` (Stage 9.B3) | 🟡 Major | **[TASK-M6]** |
| Missing Modal Schema for "Log Monthly Revenue" | `egm-implementation-readiness` (Stage 9.A6) | 🟡 Major | **[TASK-M7]** |
| Minor-Unit Terminology & Error Terminology | `egm-style` (Stage 9.C1, 9.C2), `egm-implementation-readiness` (Stage 9.A7), `egm-codebase-details` (Stage 9.B10) | 🟡 Major | **[TASK-M8]** |
| Blueprint Heading Nomenclature | `egm-structural` (Stage 7.B2) | 🟢 Nit | **[TASK-N1]** |
| Redundant Composite Index | `egm-design` (Stage 7.A7), `egm-structural` (Stage 7.B3), `egm-zoom-out` (Stage 5.7), `egm-comprehension` (Stage 6.9) | 🟢 Nit | **[TASK-N2]** |
| Hyperbole, Slang, and Promotional Fluff | `egm-style` (Stage 7.C2, 7.C3, 7.C4, 7.C5, Stage 9.C3) | 🟢 Nit | **[TASK-N3]** |
| Malformed LaTeX Math Mode Syntax | `egm-structural` (Stage 7.B4) | 🟢 Nit | **[TASK-N4]** |
| Unlinked File Paths & Domain Acronyms | `egm-structural` (Stage 7.B5), `egm-comprehension` (Stage 6.8), `egm-design` (Stage 7.A8) | 🟢 Nit | **[TASK-N5]** |
| Missing Phase Verification Checkpoints | `egm-implementation-readiness` (Stage 9.A8) | 🟢 Nit | **[TASK-N6]** |
| Sitemap Registration Reconciled | `egm-codebase-details` (Stage 9.B9) | 🟢 Nit | **[TASK-N7]** |

---

## Conclusion & Recommended Next Step

The Goldfish Review Protocol has successfully identified and isolated all critical failure modes across the feature specification and implementation plan. 

The document author should now execute:
```
/egm-resolve docs/designs/doctor_portfolio.md
```
This will activate the primary Elephant resolver context to evaluate the consolidated findings, approve technical fixes, and apply the required textual and architectural updates to [`docs/designs/doctor_portfolio.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_portfolio.md).
