# Stage 5: Initial Zoom Out Review Report

**Target File:** `docs/designs/doctor_portfolio.md`  
**Target Scope:** Full Document (Lines 1–418)  
**Standard:** `resources/document_standards/zoom-out-standard.md`  
**Reviewer:** `egm-zoom-out` (Skeptical Architect Goldfish)  
**Date:** 2026-11-01  
**Overall Verdict:** **FAIL**

---

## Executive Summary
While the high-level objective is well-motivated and solves a genuine business problem (commercial visibility over physician detailing spend), the document suffers from a critical architectural contradiction in its core query design, date-granularity calculation errors, temporal accounting inconsistencies, and missed opportunities to leverage existing codebase and framework capabilities.

---

## Findings

### 1. [Blocker] Primary Sorting Column (`net_pnl`) is Missing from the SQL Query Service
* **Affected Scope:** Section 4 (lines 201–255), Section 5 (lines 281–283), and Section 7.3 (line 359)
* **Simplicity Rationale:** 
  The core value proposition and primary user interaction in the design is sorting doctors by `net_pnl DESC` natively in SQLite (lines 283, 313, 359, 410) without N+1 memory loading.
  However, the provided `DoctorPortfolioQueryService::getBaseQuery()` implementation (lines 218–253) only selects `visits_count`, `samples_cost`, `promos_cost`, and `total_revenue`. It **never computes or selects `total_investment` or `net_pnl`**.
  In SQLite and standard SQL, select aliases from the same SELECT clause cannot be referenced in sibling expressions (e.g., `total_revenue - (visits_count * cost + samples_cost + promos_cost)` cannot use aliases defined in the same SELECT statement). When Filament attaches `->defaultSort('net_pnl', 'desc')`, SQLite will fail immediately at runtime with `General error: 1 no such column: net_pnl`.
  The architecture must specify how `net_pnl` and `total_investment` are projected into the query builder (e.g. via an enclosing CTE/subquery or computed expression) so native database sorting actually functions.

---

### 2. [Major] Date Granularity Mismatch Distorts Rolling Horizon Filters (`trailing_90`)
* **Affected Scope:** Section 3 (lines 176–198), Section 4 (lines 246–252), and Section 5 (lines 264–268)
* **Simplicity Rationale:** 
  DCR operational expenses are logged on exact calendar days (`d_c_r_s.date`), whereas `doctor_revenues` is normalized to the first day of each month (`YYYY-MM-01`).
  When an executive selects the `trailing_90` filter on November 1, the date window is approximately `2026-08-03` to `2026-11-01`. In the query service:
  ```php
  $query->where('doctor_revenues.period_date', '>=', '2026-08-03')
  ```
  This clause **completely drops August 2026 revenue** (`2026-08-01 < 2026-08-03`), yet the DCR subqueries will still accumulate 28 days of visit costs, samples, and promotional gifts for August 3–31. This produces severely distorted, artificially negative P&L metrics.
  The design must align date boundaries: either non-month-aligned filters must snap `startDate` to `startOfMonth()` for revenue or date filtering must operate strictly on closed calendar months.

---

### 3. [Major] Architectural Contradiction on Historical Cost Mutability
* **Affected Scope:** Open Questions (lines 57–58), CUJ 3 (lines 75–79), and Section 4 (lines 228–245)
* **Simplicity Rationale:** 
  CUJ 3 states: *"When the admin updates the unit cost on PromotionalInputResource... New DCR activities reflect the updated cost baselines immediately in future calculations."*
  This assertion contradicts the technical implementation. The proposed query service joins live catalog prices (`products.sample_cost`, `promotional_inputs.unit_cost`, and live `config('exponit.default_visit_cost')`) directly against historical DCR logs.
  Updating a gift cost or default visit cost today will **retroactively alter all past years' and closed quarters' calculated P&L**. 
  While dynamic catalog pricing is an acceptable simplification for a v1 MVP to avoid point-in-time snapshot tables, the design cannot falsely claim past activities remain unaffected. The document must explicitly define whether v1 accepts floating historical accounting or if snapshotting is required.

---

### 4. [Major] Unique Constraint on `(doctor_id, period_date)` Breaks Multi-Chemist Sales Attribution
* **Affected Scope:** Migration 4 (lines 140–155) and Section 7.3 (lines 364–366)
* **Simplicity Rationale:** 
  Migration 4 enforces `$table->unique(['doctor_id', 'period_date'])`.
  In pharmaceutical operations, prescription revenue audits frequently arrive from multiple retail chemists or stockists within the same month for a single doctor (e.g., Chemist A: ₹4,000, Chemist B: ₹6,000 for October 2026).
  Enforcing a database-level unique constraint forces users to manually pre-aggregate all sales outside the application before uploading. If a multi-row CSV is uploaded, subsequent entries for the same doctor in the same month will either trigger duplicate key exceptions or overwrite prior entries during upserts.
  Since the query engine already sums revenue using `COALESCE(SUM(doctor_revenues.amount), 0)`, the schema should permit multiple revenue entries per doctor per period, or explicitly specify why multi-source attribution is prohibited.

---

### 5. [Nit] Ignoring Existing `products.unit_cost` and Reinventing `sample_cost`
* **Affected Scope:** Migration 2 (lines 125–130) and Query Service (line 235)
* **Simplicity Rationale:** 
  The codebase's `products` table **already has a `unit_cost` column** (`decimal:2`), which is actively populated for products. Adding a separate `sample_cost` column that defaults to `0.00` causes all existing products with unit costs to evaluate to ₹0 sample investment unless re-entered.
  The query should reuse the existing field with a fallback: `COALESCE(products.sample_cost, products.unit_cost, 0)`, or evaluate whether `unit_cost` alone is sufficient for v1.

---

### 6. [Nit] Re-inventing Filament CSV Ingestion
* **Affected Scope:** Section 5 (line 287) and Section 7.3 (lines 364–366)
* **Simplicity Rationale:** 
  The design introduces a bespoke `DoctorRevenueImportService` for parsing CSVs and reporting validation errors. Filament v5 includes a native, robust import architecture (`Filament\Actions\ImportAction` and `Filament\Actions\Imports\Importer`) that handles CSV parsing, column mapping, validation, error handling, and downloadable failure CSVs out of the box. Building a custom import service reinvents wheel components already provided by the installed framework.

---

### 7. [Nit] Altitude Drops and Redundant Index Definition
* **Affected Scope:** Section 2 (lines 118–170) and Migration 4 (lines 151–152)
* **Simplicity Rationale:** 
  Section 2 contains raw PHP migration scripts and Section 4 contains a 50-line raw PHP method, both of which are restated again in Section 7. High-altitude specifications should focus on schema structures, data flow, and interfaces.
  Additionally, Migration 4 defines `$table->index(['doctor_id', 'period_date'])` immediately after `$table->unique(['doctor_id', 'period_date'])`, creating an identical duplicate index.

---

## Audit Vector Checklist
* **Context Insufficiency:** None. The problem, user persona, and scope are well explained.
* **Root Problem Focus:** Strong. Focuses directly on commercial account triage.
* **YAGNI Violations:** Minor speculative additions (`visit_cost_override` on `doctors` when global default exists; `erp_sync` source tag).
* **Reinventing the Wheel:** Found (`DoctorRevenueImportService` vs. Filament `ImportAction`; `sample_cost` vs. existing `products.unit_cost`).
* **Premature Optimizations:** None. Appropriately rejected materialized tables and Redis caching for a 233-doctor dataset.
* **Altitude Drops (Weeds):** Found (verbatim migration and query code duplication in architecture sections).
* **Architectural Dead-Ends / Flaws:** Critical (`net_pnl` missing from SQL query preventing sorting; date granularity mismatch in rolling filters; multi-chemist revenue overwriting).
