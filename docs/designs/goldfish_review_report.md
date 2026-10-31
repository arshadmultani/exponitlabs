# Goldfish Annotated Review Report

**Target:** `docs/designs/doctor_portfolio.md` (`full file`)  
**Resolution Date:** 2026-11-01  
**Status:** resolved  
**Accepted Fixes:** 20 (5 Blockers, 8 Majors, 7 Nits)  
**Contested Items:** 0  
**Escalated Items:** 0  
**Remaining Unaddressed Issues:** 0  

---

## Prioritized Findings & AI Suggestions

### [Issue 1] [`egm-design`, `egm-zoom-out`, `egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: High (Blocker)
* **Affected Scope:** [Lines 201–255, 358–363, 380, 411](doctor_portfolio.md#L201-L255) (`DoctorPortfolioQueryService`, `DoctorPortfolio`)
* **Discrepancy Description:** `DoctorPortfolioQueryService` omits `total_investment` and `net_pnl` from the SQL query projection, but the Filament table specifies `defaultSort('net_pnl', 'desc')` and sortable columns. In SQLite and SQL, column aliases in the same `SELECT` clause cannot be referenced by sibling expressions. At runtime, Filament's `ORDER BY net_pnl DESC` immediately throws a fatal PDO exception: `SQLSTATE[HY000]: General error: 1 no such column: net_pnl`.
* **Rationale:** The document explicitly promises native database sorting without N+1 loops, but provides an incomplete query that crashes when sorted.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Wrap the correlated subqueries in an Eloquent derived query using `fromSub()` inside `DoctorPortfolioQueryService::getBaseQuery()`. This projects real SQL columns `total_investment` and `net_pnl` directly to the outer query, enabling native SQLite sorting and pagination with zero raw migration views.
* ***Proposed Edit:***
  * Location: `### 4. SQLite Query Service` (lines 201–255) and Section 3 (lines 358–363)
  * Change: Replace the existing `getBaseQuery()` code block with the derived `fromSub()` implementation:
    ```php
    public function getBaseQuery(?Carbon $startDate = null, ?Carbon $endDate = null): Builder
    {
        $defaultVisitCost = config('exponit.default_visit_cost', 250.00);

        // Snap revenue start date to beginning of month to prevent rolling date revenue exclusion
        $revenueStartDate = $startDate ? $startDate->copy()->startOfMonth() : null;

        $visitsQuery = DCR::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $samplesQuery = DB::table('dcr_products')
            ->join('d_c_r_s', 'd_c_r_s.id', '=', 'dcr_products.dcr_id')
            ->join('products', 'products.id', '=', 'dcr_products.product_id')
            ->selectRaw('COALESCE(SUM(dcr_products.quantity * COALESCE(dcr_products.unit_cost_snapshot, products.unit_cost, 0.00)), 0)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $promosQuery = DB::table('dcr_promotional_inputs')
            ->join('d_c_r_s', 'd_c_r_s.id', '=', 'dcr_promotional_inputs.dcr_id')
            ->join('promotional_inputs', 'promotional_inputs.id', '=', 'dcr_promotional_inputs.promotional_input_id')
            ->selectRaw('COALESCE(SUM(dcr_promotional_inputs.quantity * COALESCE(dcr_promotional_inputs.unit_cost_snapshot, promotional_inputs.unit_cost, 0.00)), 0)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $revenueQuery = DoctorRevenue::query()
            ->selectRaw('COALESCE(SUM(doctor_revenues.amount), 0)')
            ->whereColumn('doctor_revenues.doctor_id', 'doctors.id')
            ->when($revenueStartDate, fn ($q) => $q->where('doctor_revenues.period_date', '>=', $revenueStartDate))
            ->when($endDate, fn ($q) => $q->where('doctor_revenues.period_date', '<=', $endDate));

        $innerQuery = Doctor::query()
            ->select('doctors.*')
            ->selectSub($visitsQuery, 'visits_count')
            ->selectSub($samplesQuery, 'samples_cost')
            ->selectSub($promosQuery, 'promos_cost')
            ->selectSub($revenueQuery, 'total_revenue');

        return Doctor::query()
            ->fromSub($innerQuery, 'doctors')
            ->select('doctors.*')
            ->selectRaw('((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost) AS total_investment', [$defaultVisitCost])
            ->selectRaw('(doctors.total_revenue - ((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost)) AS net_pnl', [$defaultVisitCost]);
    }
    ```

---

### [Issue 2] [`egm-structural`, `egm-design`] - Severity: High (Blocker)
* **Affected Scope:** [Lines 321–323](doctor_portfolio.md#L321-L323) (Document Root / Pre-Implementation Boundary)
* **Discrepancy Description:** The Feature Specification Standard strictly mandates an evaluation of constraints, failure modes, security, and scalability limits. Lines 1–322 completely omit a "Risks & Mitigations" section.
* **Rationale:** Missing mandatory structural section. Critical risks (SQLite write lock contention, UCPMP regulatory exposure, historical pricing corruption, and rolling date distortions) are left unaddressed.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Insert a dedicated `## Risks & Mitigations` section before `## Detailed Implementation Plan` addressing all four core risk vectors.
* ***Proposed Edit:***
  * Location: Insert after line 321, preceding `## Detailed Implementation Plan`
  * Change: Add complete `## Risks & Mitigations` section:
    ```markdown
    ## Risks & Mitigations

    ### 1. SQLite Database Concurrency & Bulk Ingestion Locks
    * **Risk**: SQLite operates with a database-level write lock. A large monthly revenue CSV import could hold an exclusive lock, causing `database is locked` errors for mobile field reps submitting DCRs via `/mr`.
    * **Mitigation**: With SQLite WAL (Write-Ahead Logging) mode enabled in Laravel Herd, read transactions execute concurrently without blocking writes. The `DoctorRevenueImportService` performs in-memory CSV validation first and commits all valid rows in a single atomic transaction executing `DoctorRevenue::upsert()` in under 20ms for 250 rows, eliminating long lock windows.

    ### 2. Healthcare Compliance (UCPMP) & Commercial Privacy
    * **Risk**: Correlating physician prescribing revenue with promotional input expenditure and detailing visits is legally sensitive under India's Uniform Code for Pharmaceuticals Marketing Practices (UCPMP). Exposing account profitability to unauthorized staff creates compliance risk.
    * **Mitigation**: Access to `/console/doctor-portfolio` is restricted via Filament page authorization (`canAccess()`), limiting access strictly to executive and administrative roles (`SuperAdmin`, `SalesDirector`). The mobile field force application (`/mr`) has zero access to this page or financial calculations.

    ### 3. Historical P&L Corruption via Price Changes
    * **Risk**: Modifying a promotional gift or product cost in the master catalog could retroactively alter closed quarterly and annual P&L statements.
    * **Mitigation**: Add a nullable `unit_cost_snapshot` (`decimal:10,2`) column to `dcr_products` and `dcr_promotional_inputs`. When logging a DCR, the active catalog price is snapshotted. The query engine evaluates `COALESCE(unit_cost_snapshot, live_cost, 0.00)`, protecting historical accounting while allowing legacy records without snapshots to resolve cleanly.

    ### 4. Temporal Alignment Between Monthly Revenues and Daily Logs
    * **Risk**: Daily DCR logs combined with monthly normalized revenues (`YYYY-MM-01`) create artificial deficits when applying rolling intra-month filters (e.g., `trailing_90` resolving to intra-month).
    * **Mitigation**: Replace rolling daily windows with calendar-aligned filters (`last_3_months`, `current_fy`, `last_month`, `all_time`), and clamp query start dates for monthly revenue to `startOfMonth()`.
    ```

---

### [Issue 3] [`egm-style`, `egm-design`, `egm-comprehension`, `egm-implementation-readiness`] - Severity: High (Blocker)
* **Affected Scope:** [Lines 14–17, 192–198, 284, 377–380](doctor_portfolio.md#L14-L17) (Overview, Formulations, Prescriber Ledger)
* **Discrepancy Description:** Prescriber performance tiers (`High-Yield`, `Balanced`, `Under-Yielding`, `Incubation`) rely on subjective prose (*"significantly exceeds"*, *"cross threshold limits"*) without numerical boundaries, precedence order, or boolean expressions.
* **Rationale:** Violates the Zero-Decision Rule and Plain-English standard. An engineer cannot implement badge logic without inventing business thresholds.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Define explicit quantitative rules, precedence order, and numeric boundaries in Section 3 and Section 5.
* ***Proposed Edit:***
  * Location: Lines 14–17, lines 192–198, and line 284
  * Change: Specify deterministic tier evaluation rules:
    ```markdown
    #### Performance Tier Classification Logic
    Each physician is classified into exactly one deterministic tier evaluated in strict precedence order:
    1. **Incubation** (Badge: Info / Gray):
       - *Condition:* `doctors.created_at >= now()->subDays(60)`
       - *Rationale:* New physicians undergoing relationship ramp-up are exempted from profitability scoring during their first 60 days.
    2. **High-Yield Prescriber** (Badge: Success / Green):
       - *Condition:* `net_pnl > 0` AND `roi_percentage >= 50.0`
       - *Rationale:* Accounts where commercial sales exceed total invested detailing capital by 50% or more.
    3. **Balanced Prescriber** (Badge: Warning / Amber):
       - *Condition:* `net_pnl >= 0` AND `roi_percentage < 50.0`
       - *Rationale:* Accounts generating positive net margin that covers ongoing visit and detailing costs but remains below the 50% ROI growth target.
    4. **Under-Yielding Prescriber** (Badge: Danger / Red):
       - *Condition:* `net_pnl < 0`
       - *Rationale:* Accounts where promotional inputs, samples, and visit expenditures exceed attributed prescription revenue.
    ```

---

### [Issue 4] [`egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: High (Blocker)
* **Affected Scope:** [Lines 70–74, 364–366, 381–382, 414–418](doctor_portfolio.md#L70-L74) (`DoctorRevenueImportService`, `DoctorPortfolio`)
* **Discrepancy Description:** `DoctorRevenueImportService` is introduced without defining the CSV column headers, date parsing rules, service method signatures, return structures, or transaction semantics.
* **Rationale:** Violates the Re-Implementation Standard and Zero-Decision Rule.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Specify the complete technical ingestion contract in Section 3 and Section 4.
* ***Proposed Edit:***
  * Location: Lines 364–366 and lines 381–382
  * Change: Add the explicit service interface, CSV schema, and transaction strategy:
    ```markdown
    ### `app/Services/DoctorRevenueImportService.php` [Create]
    * **CSV Column Schema**:
      - `doctor_id` (Integer, required): Foreign key matching `doctors.id`.
      - `period_date` (String, optional): Date formatted as `YYYY-MM` or `YYYY-MM-DD`. If omitted or blank, falls back to the `$fallbackPeriod` selected in the modal. Normalized to `YYYY-MM-01`.
      - `amount` (Decimal, required): Monthly revenue in ₹ (e.g., `4500.00`).
      - `notes` (String, nullable): Chemist, stockist, or invoice reference notes.
    * **Method Signature**:
      ```php
      public function import(UploadedFile $file, ?Carbon $fallbackPeriod = null): RevenueImportResult
      ```
    * **Processing Pipeline**:
      1. Parse CSV rows in memory and validate:
         - Ensure `doctor_id` exists in `doctors` table.
         - Ensure `amount` is numeric and greater than 0.
         - Ensure valid period date is resolved (from row or fallback).
      2. If validation errors occur, return a structured `RevenueImportResult` containing row-level error messages (`row_index`, `message`).
      3. Commit valid records in a single `DB::transaction()` executing `DoctorRevenue::insert()` (allowing multi-chemist entries per doctor per month).
    ```

---

### [Issue 5] [`egm-codebase-details`, `egm-zoom-out`] - Severity: High (Blocker)
* **Affected Scope:** [Lines 125–130, 330–331, 347–348, 373–374](doctor_portfolio.md#L125-L130) (`products` migration, `Product.php`, `ProductForm.php`)
* **Discrepancy Description:** The plan proposes a new `sample_cost` column on `products`. In the actual codebase, `products` already has a populated `unit_cost` column (`decimal:2`) exposed in `ProductForm.php`. Proposing `sample_cost` creates schema duplication and ignores existing product costs.
* **Rationale:** Codebase drift and reinventing existing fields.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Reuse `products.unit_cost` as the primary cost baseline. Add `COALESCE(products.sample_cost, products.unit_cost, 0.00)` to ensure compatibility if a company later wishes to set a distinct sample subsidy, but prevent breaking existing catalog costs.
* ***Proposed Edit:***
  * Location: Lines 125–130, 330–331, 347–348, and 373–374
  * Change: Clarify that `products.unit_cost` already exists in `products` table. Migration 1 adds `sample_cost` as a nullable override (`$table->decimal('sample_cost', 10, 2)->nullable()->after('unit_cost')`). If `sample_cost` is null, calculations fall back to `unit_cost`.

---

### [Issue 6] [`egm-zoom-out`, `egm-design`, `egm-comprehension`, `egm-codebase-details`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 176–198, 246–252, 266, 358–366](doctor_portfolio.md#L176-L198) (Date Filtering & Horizon Definitions)
* **Discrepancy Description:** Rolling intra-month date filters (e.g., `trailing_90` resolving to Aug 3) exclude August monthly revenue stored as `2026-08-01` (`2026-08-01 >= 2026-08-03` is false), while including 28 days of August DCR expenses.
* **Rationale:** Creates false-negative P&L distortions on rolling horizons.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Replace `trailing_90` with calendar-aligned `last_3_months`, and clamp `$startDate` for monthly revenue queries to `$startDate->copy()->startOfMonth()`.
* ***Proposed Edit:***
  * Location: Lines 266 and 358–366
  * Change: Replace `trailing_90` with `last_3_months` (evaluating from 1st of month 3 months prior through end of previous closed month, or current date). In `DoctorPortfolioQueryService`, apply `$revenueStartDate = $startDate ? $startDate->copy()->startOfMonth() : null;`.

---

### [Issue 7] [`egm-design`, `egm-zoom-out`, `egm-comprehension`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 57–58, 75–78, 228–245](doctor_portfolio.md#L57-L58) (Historical Pricing Mutability)
* **Discrepancy Description:** CUJ 3 asserts that updating promotional or sample catalog prices will only affect future calculations. However, live query joins to master catalog tables retroactively mutate past closed quarters' accounting.
* **Rationale:** Architectural contradiction.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Add a nullable `unit_cost_snapshot` (`decimal:10,2`) column to `dcr_products` and `dcr_promotional_inputs`. Populate it upon DCR logging, with fallback: `COALESCE(dcr_products.unit_cost_snapshot, products.sample_cost, products.unit_cost, 0.00)`.
* ***Proposed Edit:***
  * Location: Insert migration in Section 1 and update DCR subqueries in Section 4.

---

### [Issue 8] [`egm-zoom-out`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 140–155, 334–335, 364–366](doctor_portfolio.md#L140-L155) (Migration 4 & `DoctorRevenue`)
* **Discrepancy Description:** Migration 4 enforces a unique constraint on `(doctor_id, period_date)`. In real pharmaceutical operations, prescription audits arrive from multiple chemists within the same month for a single doctor. The unique constraint prevents multi-source attribution or triggers upsert collisions.
* **Rationale:** Domain mismatch and data collision hazard.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Drop `$table->unique(['doctor_id', 'period_date'])`, replacing it with an index `$table->index(['doctor_id', 'period_date'])`. The query engine already aggregates with `SUM(amount)`.
* ***Proposed Edit:***
  * Location: Migration 4 definition (lines 151–152) and Section 1 (line 335)
  * Change: Replace unique constraint with standard composite index.

---

### [Issue 9] [`egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 269–274, 382–384](doctor_portfolio.md#L269-L274) (`DoctorPortfolioStatsWidget`, `DoctorPortfolio`)
* **Discrepancy Description:** Card 4 is contradictorily defined ("Account Efficiency Ratio" vs. "Overall Return Multiple"). Furthermore, the plan does not define the Livewire/Filament communication mechanism to pass the active time horizon from the page to the header widget.
* **Rationale:** Missing UI state binding contract and metric contradiction.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Standardize Card 4 as "Overall Return Multiple" (`Total Attributed Revenue / Total Invested Capital`, with zero-guard returning `0.0x`). Pass active horizon via widget parameter `$widgetData = ['horizon' => $this->activeHorizon]`.
* ***Proposed Edit:***
  * Location: Section 5 (lines 269–274) and Section 4 (lines 382–384)
  * Change: Specify Card 4 formula, zero-guard, and `$widgetData` state passing.

---

### [Issue 10] [`egm-implementation-readiness`, `egm-codebase-details`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 402–407](doctor_portfolio.md#L402-L407) (Automated Verification Plan)
* **Discrepancy Description:** `DoctorFinancialMetricsTest.php` in `tests/Unit` tests calculations, but no `DoctorFinancialMetrics` class exists. All calculations are executed in SQL inside `DoctorPortfolioQueryService`. Unit tests cannot boot database queries.
* **Rationale:** Phantom class under test.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Relocate test suite to database-backed feature test: `tests/Feature/Services/DoctorPortfolioQueryServiceTest.php` using `RefreshDatabase`. Add `database/factories/DoctorRevenueFactory.php` to Section 2 and Section 6.
* ***Proposed Edit:***
  * Location: Section 6 (lines 402–407)
  * Change: Rename test file and add factory specification.

---

### [Issue 11] [`egm-codebase-details`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 328–418](doctor_portfolio.md#L328-L418) (Sections 1 through 6)
* **Discrepancy Description:** Scope files in the implementation plan are listed as bare filepaths without explicit action modifiers (`[Create]`, `[Modify]`, `[Delete]`).
* **Rationale:** Violates Implementation Readiness Standard.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Prefix every file item in lines 328–418 with explicit `[Create]` or `[Modify]` tags.
* ***Proposed Edit:***
  * Location: Sections 1 through 6 (lines 328–418)
  * Change: Prefix each entry with `[Create]` or `[Modify]`.

---

### [Issue 12] [`egm-implementation-readiness`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 377–381](doctor_portfolio.md#L377-L381) (`DoctorPortfolio.php`)
* **Discrepancy Description:** The plan requires a "Log Monthly Revenue" header action but provides no form schema or validation constraints.
* **Rationale:** Leaves UI schema design to the implementer.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Document the explicit Filament action modal schema: `doctor_id` (Select, required), `period_date` (DatePicker, Y-m-d, required), `amount` (TextInput, numeric, min 0.01, required), `notes` (TextInput, nullable).
* ***Proposed Edit:***
  * Location: Lines 377–381
  * Change: Add explicit Filament Action schema.

---

### [Issue 13] [`egm-style`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: Medium (Major)
* **Affected Scope:** [Lines 352, 406](doctor_portfolio.md#L352) (`DoctorRevenue.php`, `DoctorFinancialMetricsTest.php`)
* **Discrepancy Description:** Line 352 refers to "minor-unit formatting helpers" on a `decimal(12, 2)` column. Line 406 references "arithmetic overflow" instead of PHP 8 `DivisionByZeroError`.
* **Rationale:** Inaccurate domain terminology.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Replace minor-unit references with standard decimal rupee formatting, and reference `DivisionByZeroError`.
* ***Proposed Edit:***
  * Location: Line 352 and line 406
  * Change: Update terminology to standard decimal formatting and `DivisionByZeroError`.

---

### [Issue 14] [`egm-structural`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 21, 32, 48, 82](doctor_portfolio.md#L21) (Blueprint Section Headings)
* **Discrepancy Description:** Non-standard section headings (`## The Problem`, split `## Goals` / `## Non-Goals`, `## Technical Plan (Architecture)`).
* **Rationale:** Structural layout symmetry.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Align with canonical headings: `## Problem Statement`, `## Goals & Non-Goals`, `## Proposed Architecture`.
* ***Proposed Edit:***
  * Location: Lines 21, 32, 48, 82
  * Change: Update heading names.

---

### [Issue 15] [`egm-design`, `egm-structural`, `egm-zoom-out`, `egm-comprehension`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 151–153](doctor_portfolio.md#L151-L153) (Migration 4)
* **Discrepancy Description:** Duplicate composite index definition on `(doctor_id, period_date)`.
* **Rationale:** Redundant schema definition.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Retain single composite index `$table->index(['doctor_id', 'period_date'])` and remove duplicate index declaration.
* ***Proposed Edit:***
  * Location: Lines 151–153
  * Change: Keep only one index definition.

---

### [Issue 16] [`egm-style`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 296, 298, 306, 310, 318, 320, 326, 331, 359, 377, 380, 408](doctor_portfolio.md#L296) (Prose Style)
* **Discrepancy Description:** Hyperbolic claims ("severe cross-stack coupling", "N+1 query explosion", "100% of requirements"), jargon slang ("YAGNI"), and marketing descriptors ("executive", "high-performance", "standard").
* **Rationale:** Plain-English standard and fluff elimination.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Replace emotional rhetoric with neutral, factual engineering prose.
* ***Proposed Edit:***
  * Location: Across indicated lines
  * Change: Streamline text and replace with objective phrasing.

---

### [Issue 17] [`egm-structural`] - Severity: Low (Nit)
* **Affected Scope:** [Line 178](doctor_portfolio.md#L178) (LaTeX Math)
* **Discrepancy Description:** Unescaped double question marks and single quotes in LaTeX math block.
* **Rationale:** Parser rendering warnings in KaTeX.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Wrap strings and operators cleanly in `\text{...}` blocks.
* ***Proposed Edit:***
  * Location: Line 178
  * Change: Clean LaTeX syntax.

---

### [Issue 18] [`egm-structural`, `egm-comprehension`, `egm-design`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 6, 12, 14, 39, 50, 77, 204, 293–304](doctor_portfolio.md#L6) (Links and Acronyms)
* **Discrepancy Description:** Unlinked entities and undefined acronyms (`UCPMP`, `ERPNext`).
* **Rationale:** Comprehensive readability.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Add markdown links and define acronyms on first mention.
* ***Proposed Edit:***
  * Location: Across indicated lines
  * Change: Add links and acronym definitions.

---

### [Issue 19] [`egm-implementation-readiness`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 328–418](doctor_portfolio.md#L328-L418) (Phasing Checkpoints)
* **Discrepancy Description:** No intermediate CLI verification commands at the end of Phases 1 through 5.
* **Rationale:** Implementation testability.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Add explicit intermediate verification commands (`php artisan migrate`, `php artisan test`) at the end of each implementation phase.
* ***Proposed Edit:***
  * Location: At each phase transition
  * Change: Add CLI verification checkpoints.

---

### [Issue 20] [`egm-codebase-details`] - Severity: Low (Nit)
* **Affected Scope:** [Lines 395–397](doctor_portfolio.md#L395-L397) (Sitemap Task)
* **Discrepancy Description:** Line 395 lists registering the design document in `docs/designs/SITEMAP.md`, which is already registered.
* **Rationale:** Task redundancy.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Note that sitemap indexing is already complete.
* ***Proposed Edit:***
  * Location: Line 395
  * Change: Mark as `(Completed)`.
