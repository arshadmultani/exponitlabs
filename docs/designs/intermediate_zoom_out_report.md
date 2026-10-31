# Stage 8: Intermediate Zoom Out Review Report

**Target File:** `docs/designs/doctor_portfolio.md`  
**Target Scope:** Full Document (Lines 1–419)  
**Standard:** `resources/document_standards/zoom-out-standard.md`  
**Reviewer:** `egm-zoom-out` (Skeptical Architect Goldfish)  
**Date:** 2026-11-01  
**Overall Verdict:** **PASS WITH RESERVATIONS** (Architectural trajectory is sound, conditional on enforcing the minimal remediation paths detailed below and rejecting enterprise over-engineering).

---

## Executive Summary & Trajectory Assessment

Following the initial Zoom Out, Comprehension, and Critic audits (Stages 5–7), the core business problem and high-level architecture remain compelling: commercial detailing triage for 233 physicians inside an existing Filament v5 console without premature ERPNext coupling. 

However, the previous stages identified legitimate technical blockers (missing `net_pnl` in SQL preventing sorting, date boundary exclusion, unversioned historical pricing, and multi-chemist CSV collisions). 

This Intermediate Zoom Out audit evaluates the **remediation trajectory**. The greatest risk facing this design is **over-correction**—introducing speculative enterprise patterns (database views, event sourcing, background queue pipelines, and chemist relation hierarchies) to solve problems that can be resolved with standard Laravel and SQLite primitives. Below is the simplicity stress-test and mandatory architectural guardrails for the upcoming revision.

---

## Remediation Stress-Test & Architecture Findings

### 1. [Major] Sortable SQL Projections (`net_pnl`): Enforce Derived Query / `fromSub()`, Reject Database VIEWs
* **Affected Scope:** Section 4 (`DoctorPortfolioQueryService`, lines 212–255) and Prescriber Ledger Table (lines 281–283)
* **Simplicity & Architecture Rationale:**
  * *The Trap:* An engineer attempting to make `total_investment` and `net_pnl` sortable in SQLite might be tempted to create a database VIEW (`CREATE VIEW doctor_portfolio_view`) via migrations, or introduce a raw SQL Common Table Expression (CTE) using the DB facade. Database views in SQLite create schema churn, complicate test environments, and decouple queries from Eloquent model hydration and Filament record keys.
  * *The Lean Solution:* Keep the solution strictly within Eloquent and `DoctorPortfolioQueryService`. In SQLite and standard SQL, alias references cannot appear in sibling SELECT expressions within the same clause. The simplest, cleanest pattern is a derived query:
    1. Define an inner query selecting `doctors.*`, `visits_count`, `samples_cost`, `promos_cost`, and `total_revenue`.
    2. Wrap it with `Doctor::query()->fromSub($innerQuery, 'doctors')` and project:
       ```php
       $query->select('*')
           ->selectRaw('((visits_count * ?) + samples_cost + promos_cost) AS total_investment', [$defaultVisitCost])
           ->selectRaw('(total_revenue - ((visits_count * ?) + samples_cost + promos_cost)) AS net_pnl', [$defaultVisitCost]);
       ```
    This generates standard database columns `total_investment` and `net_pnl` that Filament's native `->defaultSort('net_pnl', 'desc')` can sort directly in SQLite, with zero migration views, zero raw DB bypasses, and 100% Eloquent model compatibility.

---

### 2. [Major] SQLite Concurrency & CSV Ingestion: Reject Queue/Chunking Over-Engineering
* **Affected Scope:** Section 1 CUJ 2 (lines 70–74), Section 4 (lines 364–366), and Critic Finding A.6
* **Simplicity & Architecture Rationale:**
  * *The Trap:* The Critic report raised concerns about SQLite database write locks during CSV imports, recommending chunked transactions, queue workers, and retry loops. Applying asynchronous background queues, Redis workers, and batch polling for Exponit Labs is a blatant **YAGNI violation**.
  * *The Reality:* There are only 233 doctors in the entire system. A monthly chemist prescription audit file contains between 50 and 500 rows. In SQLite with WAL mode (standard in Laravel/Herd), inserting 250 rows in a single prepared transaction takes **under 15 milliseconds**. Chunking a 200-row file into batches with delays actually *prolongs* the lock window.
  * *The Lean Solution:* 
    1. Reject background queue workers and chunking pipelines.
    2. Leverage Filament's native `ImportAction` / `Importer` (or a single atomic `DB::transaction()` executing `DoctorRevenue::upsert()`).
    3. Execute validation in memory first, then write the batch in a single sub-20ms transaction. This completely avoids SQLite lock contention without introducing queue infrastructure.

---

### 3. [Major] Historical Pricing Immutability: Nullable Snapshot Column vs. Temporal Event-Sourcing
* **Affected Scope:** Open Question 1 (line 58), CUJ 3 (lines 75–78), and Critic Finding A.3
* **Simplicity & Architecture Rationale:**
  * *The Trap:* To resolve the contradiction where changing a promotional gift or product cost today recalculates historical quarters, an architect might reach for complex temporal tables (`price_histories`), date-range joins (`effective_from`, `effective_to`), or event sourcing. This would triple the schema complexity for zero practical gain.
  * *The Lean Solution:* 
    * Add a single nullable `unit_cost_snapshot` (`decimal:10,2`) column to `dcr_products` and `dcr_promotional_inputs`.
    * When a DCR is created or updated, snapshot the active product/input price:
      `$dcrProduct->unit_cost_snapshot = $product->unit_cost;`
    * In `DoctorPortfolioQueryService`, use a simple fallback:
      `COALESCE(dcr_products.unit_cost_snapshot, products.unit_cost, 0)`
    * Legacy records with `NULL` seamlessly fall back to master catalog pricing. Zero new tables, zero event sourcing, zero date-range joins.

---

### 4. [Major] Date Boundary Alignment: Reject Pro-Rating, Snap to Closed Fiscal Months
* **Affected Scope:** Section 3 (lines 176–198), Section 4 (lines 246–252), and Section 5 (line 266)
* **Simplicity & Architecture Rationale:**
  * *The Trap:* Attempting to align rolling intra-month dates (e.g. `trailing_90` resolving to Aug 3) with monthly normalized revenue (`2026-08-01`) by building daily fractional pro-rating logic (`amount * (28 / 31)`). This adds fragile arithmetic, rounding anomalies, and accounting inaccuracies.
  * *The Lean Solution:* In pharmaceutical commercial operations, prescription revenue audits are inherently monthly. The rolling `trailing_90` days filter is the sole outlier (the other 3 presets—`current_fy`, `last_month`, `all_time`—are already calendar-month aligned).
    * Replace `trailing_90` with **`last_3_months`** (or snap any non-first-of-month `$startDate` to `$startDate->copy()->startOfMonth()`).
    * Aligning date boundaries at the query input or preset level completely eliminates the data distortion without a single line of fractional pro-rating code.

---

### 5. [Major] Multi-Chemist Sales Attribution: Drop the Unique Constraint Instead of Adding Chemist Tables
* **Affected Scope:** Migration 4 (lines 140–155) and Zoom Out Finding 4
* **Simplicity & Architecture Rationale:**
  * *The Trap:* In real-world clinics, prescription audits arrive from multiple chemists (e.g., Chemist A: ₹4,000, Chemist B: ₹6,000 for Dr. Sharma in October). To support multi-chemist attribution, an engineer might design a full `chemists` or `stockists` entity with relational links, routing tables, and master CRUD interfaces.
  * *The Lean Solution:* Resist expanding domain scope. 
    * Simply **drop the database-level unique constraint** `$table->unique(['doctor_id', 'period_date'])` in `doctor_revenues`, replacing it with an index.
    * The query service already sums revenue with `COALESCE(SUM(doctor_revenues.amount), 0)`.
    * By allowing multiple rows per doctor per month (using the existing `notes` or `source` column for the chemist name or invoice reference), the system handles multiple revenue sources out of the box with zero schema bloat.

---

### 6. [Nit] Codebase Reuse: Leverage Existing `products.unit_cost` Instead of Forcing `sample_cost`
* **Affected Scope:** Migration 2 (lines 125–130) and Query Service (line 235)
* **Simplicity & Architecture Rationale:**
  * The codebase's `products` table **already has a populated `unit_cost` column** (`decimal:2`).
  * Migration 2 introduces a redundant `sample_cost` column defaulting to `0.00`. If deployed as written, all existing 233 doctors will calculate with ₹0 sample investment unless an administrator manually re-enters sample costs for every catalog item.
  * Reuse the existing field with a fallback: `COALESCE(products.sample_cost, products.unit_cost, 0.00)` or drop `sample_cost` entirely if commercial samples share identical unit economics with saleable stock.

---

## Summary Verdict & Path to Revision

**Verdict:** **PASS WITH RESERVATIONS**

The high-altitude architecture is well-scoped, appropriately uncoupled from ERPNext, and fits neatly into Filament v5 and SQLite. The reservations are strictly contingent on the author adopting the minimal, pragmatic remediation paths specified above during the document revision, while actively rejecting:
1. Database VIEW migrations or raw DB CTEs (use Eloquent `fromSub()`).
2. Asynchronous background queue pipelines or chunking loops (use sub-20ms atomic bulk transactions).
3. Temporal event-sourcing or price history ledgers (use a nullable `unit_cost_snapshot` column).
4. Daily fractional revenue pro-rating (snap filters to monthly boundaries).
5. New Chemist/Stockist master data schemas (drop the composite unique constraint).

If these guardrails are respected during document revision, the feature will remain clean, robust, and completely faithful to the Elephant-Goldfish Model and YAGNI.
