# Stage 7: Critic Review Report

**Target File:** `docs/designs/doctor_portfolio.md`  
**Target Scope:** FeatureRange (Lines 1–322)  
**Standard:** `resources/document_standards/feature-spec-standard.md`  
**Reviewers:** `egm-design` (Hostile Design Critic), `egm-structural` (Structural Layout Validator), `egm-style` (Technical Copy Editor)  
**Date:** 2026-11-01  
**Overall Verdict:** **FAIL**

---

## Executive Summary
The specification provides a clear business motivation and sensible domain decoupling (avoiding premature ERPNext coupling). However, it fails the mandatory **Feature Specification Standard** due to the total absence of a **Risks & Mitigations** section. Furthermore, it contains fatal architectural flaws: the query service omits the very computed columns required for Filament's default sorting (causing runtime SQL exceptions), introduces historical P&L corruption via unversioned pricing, and causes data exclusion bugs on rolling time windows.

---

## Vector Findings

### A. Design Critic Vector (`egm-design`)

#### 1. [Blocker] Total Absence of Mandatory "Risks & Mitigations" Section
* **Affected Scope:** Document Structure / Compliance with Target Standard
* **Finding & Recommendation:**
  * *Flaw:* The Target Standard explicitly mandates: *"The specification must explicitly call out its own constraints. It must identify potential failure modes, security implications, scalability limits, and how they will be mitigated."* The document contains no "Risks & Mitigations" or "Risks & Limits" section whatsoever. Critical risks such as SQLite database write-locking during CSV uploads, regulatory exposure under pharmaceutical marketing codes (e.g., UCPMP in India), and data sync lag are left unanalyzed.
  * *Recommendation:* Insert a dedicated "Risks & Mitigations" section covering SQLite concurrency, regulatory and privacy boundaries, historical pricing immutability, and data integrity safeguards.

#### 2. [Blocker] Query Service Omits Sortable Columns (`net_pnl`, `total_investment`), Breaking Filament Table
* **Affected Scope:** Section 4 (`DoctorPortfolioQueryService`, lines 212–255) & Section 5 (Prescriber Ledger Table, lines 281–283)
* **Finding & Recommendation:**
  * *Flaw:* The prescriber table marks `net_pnl` as sortable with `defaultSort('net_pnl', 'desc')`, and marks `total_investment` as sortable. The spec explicitly rejects PHP virtual accessors in Alternative C because *"Filament Table pagination cannot sort on PHP-computed virtual accessors in SQL"*. However, the proposed `DoctorPortfolioQueryService::getBaseQuery()` only defines subqueries for `visits_count`, `samples_cost`, `promos_cost`, and `total_revenue`. It **never defines `total_investment` or `net_pnl` in the SQL `SELECT` list**. When Filament renders the page and applies `ORDER BY net_pnl DESC`, SQLite will throw a fatal query exception: `SQLSTATE[HY000]: no such column: net_pnl`.
  * *Recommendation:* Update `DoctorPortfolioQueryService` to compute `total_investment` and `net_pnl` directly within SQL (e.g., by wrapping the correlated subqueries in a CTE / derived table or defining the composite SQL expression) so that `net_pnl` and `total_investment` exist as valid database columns for Filament's `orderBy()` clauses.

#### 3. [Major] Retroactive Historical P&L Corruption via Unversioned Master Catalog Costs
* **Affected Scope:** Section 2 (Migrations 1 & 2, lines 118–131), Section 3 (Formulations, lines 182–184), and CUJ 3 (lines 75–78)
* **Finding & Recommendation:**
  * *Flaw:* `sample_cost` is added to `products` and `unit_cost` to `promotional_inputs`. The subqueries calculate historical sample and promo spend by joining `dcr_products` and `dcr_promotional_inputs` directly to the live catalog tables. In CUJ 3, the spec claims: *"New DCR activities reflect the updated cost baselines immediately in future calculations."* This assertion is false. Because the calculation lacks price snapshotting, changing a sample or promotional input price today **retroactively recalculates and mutates all closed historical accounting periods (e.g., Q1 2024)**. Leaving this as an "Open Question" (line 58) while asserting forward-only behavior in CUJ 3 is an unacceptable architectural contradiction.
  * *Recommendation:* Add a `unit_cost_snapshot` column to `dcr_products` and `dcr_promotional_inputs` populated upon DCR submission, or explicitly acknowledge in the design that v1 uses dynamic catalog valuation and amend CUJ 3 to state that historical P&L will shift retroactively when catalog prices change.

#### 4. [Major] Rolling Period Filter (`trailing_90`) Drops Monthly Attributed Revenue
* **Affected Scope:** Section 2 (Migration 4, line 144), Section 4 (Query Service, lines 249–250), and Section 5 (Filter Bar, line 266)
* **Finding & Recommendation:**
  * *Flaw:* Revenue records are normalized to the 1st of the month (`period_date = 'YYYY-MM-01'`). When the user selects `trailing_90` (rolling 90 days), `$startDate` resolves to an intra-month date (e.g., if today is Nov 1, 90 days prior is Aug 3). The query filter `WHERE period_date >= '2026-08-03'` evaluates `2026-08-01 >= 2026-08-03` as false, completely dropping August 2026 revenue. Meanwhile, daily DCR detailing costs from Aug 3 through Aug 31 **are** included. This causes a severe false-negative P&L distortion across rolling date horizons.
  * *Recommendation:* Align date boundaries in `DoctorPortfolioQueryService`: for monthly revenue aggregations, clamp `$startDate` to `$startDate->copy()->startOfMonth()`, or replace rolling daily windows with discrete calendar periods (Current FY, Last Closed Month, Last Quarter, All Time).

#### 5. [Major] Subjective Tier Definitions and Undefined Portfolio Return Multiple
* **Affected Scope:** Overview (lines 14–17), Section 3 (Formulations, lines 192–198), and Section 5 (Widget & Table, lines 272, 284)
* **Finding & Recommendation:**
  * *Flaw:* Performance tiers are described with qualitative hand-waving (*"significantly exceeds invested detailing capital"*, *"cross threshold limits"*), providing no deterministic boolean expressions or thresholds for engineering implementation. Furthermore, the Stats Overview Widget specifies an "Overall Return Multiple" card, but Section 3 never provides a mathematical formula or zero-investment fallback for this metric. Finally, the "Incubation" tier (<60 days) does not state which timestamp is evaluated (`doctors.created_at` vs. first DCR date) or whether it overrides "Under-Yielding".
  * *Recommendation:* Provide explicit programmatic threshold logic for all tiers (e.g., `High-Yield`: ROI > 50%; `Balanced`: ROI between 0% and 50%; `Under-Yielding`: ROI < 0%; `Incubation`: `created_at >= now()->subDays(60)` regardless of ROI). Define the exact formula and zero-guard for the "Overall Return Multiple".

#### 6. [Major] SQLite Write Lock Contention and Lack of CSV Batch Semantics
* **Affected Scope:** CUJ 2 (lines 70–74) and Technical Architecture (lines 88–95, 364–366)
* **Finding & Recommendation:**
  * *Flaw:* SQLite maintains a single-writer lock. When a user uploads a large monthly revenue CSV, executing an unchunked transaction while mobile field reps are concurrently logging DCRs from `/mr` introduces high risk of `SQLSTATE[HY000]: General error: 5 database is locked` errors. The spec lacks transaction boundary definitions, chunk sizes, memory limits, and error handling strategies (atomic all-or-nothing vs. row-by-row error reporting).
  * *Recommendation:* Specify that `DoctorRevenueImportService` processes CSVs in bounded chunks (e.g., 100 rows per transaction), uses explicit database transaction retries with busy timeout handling, and returns structured row-level validation feedback.

#### 7. [Nit] Redundant Composite Index in Migration 4
* **Affected Scope:** Section 2 (Migration 4, lines 151–153)
* **Finding & Recommendation:**
  * *Flaw:* Migration 4 defines `$table->unique(['doctor_id', 'period_date'], 'unique_doctor_period_revenue');` and immediately follows it with `$table->index(['doctor_id', 'period_date']);`. In SQLite and MySQL, a unique constraint automatically constructs a composite B-tree index on those columns. The second index is 100% redundant, wasting disk space and slowing down write operations.
  * *Recommendation:* Remove `$table->index(['doctor_id', 'period_date']);`, retaining only the unique constraint and the single-column `$table->index('period_date');`.

#### 8. [Nit] Missing Role-Based Authorization and UCPMP Compliance Guardrails
* **Affected Scope:** Non-Goals (line 50) and Presentation Architecture (lines 260–264)
* **Finding & Recommendation:**
  * *Flaw:* While Non-Goals exclude field reps on `/mr`, the spec does not define Filament authorization policies for `/console/doctor-portfolio`. Under healthcare regulatory frameworks (e.g., India's Uniform Code for Pharmaceuticals Marketing Practices / UCPMP), correlating physician commercial prescription yields with promotional gifts and travel spend is highly sensitive. Exposing this page to any generic administrative console user without explicit role checks is an audit vulnerability.
  * *Recommendation:* Specify a Filament authorization check (e.g., `canAccessDoctorPortfolio`) restricting the route and stats widget to authorized roles (e.g., `SuperAdmin`, `SalesDirector`).

---

### B. Structural Vector (`egm-structural`)

#### 1. [Blocker] Missing Mandatory Section: "Risks & Mitigations"
* **Affected Scope:** Document Root / Missing Section (Expected after line 321)
* **Discrepancy Description & Rationale:**
  The Feature Specification Standard explicitly mandates that every specification must call out its constraints, identifying potential failure modes, security implications, and scalability limits alongside explicit mitigations. Lines 1..322 completely omit this section.

#### 2. [Nit] Non-Standard Heading Nomenclature for Blueprint Sections
* **Affected Scope:** Lines 21, 32, 48, 82 (`## The Problem`, `## Goals`, `## Non-Goals`, `## Technical Plan (Architecture)`)
* **Discrepancy Description & Rationale:**
  The blueprint mandates standard section headers:
  - Line 21 uses `## The Problem` instead of canonical `## Problem Statement`.
  - Lines 32 and 48 split `Goals & Non-Goals` into two disjoint H2 headings rather than unifying them under a single `## Goals & Non-Goals` section.
  - Line 82 uses `## Technical Plan (Architecture)` instead of canonical `## Proposed Architecture`.

#### 3. [Nit] Inconsistent List Bullet Syntax Across Adjacent Sections
* **Affected Scope:** Lines 3–6, 15–17, 58–59, 261–287, 294–320
* **Discrepancy Description & Rationale:**
  Bullet markers inconsistently alternate between asterisks (`*`) and hyphens (`-`). Metadata, Open Questions, CUJs, and Alternatives use `*`, whereas Overview Tiers and Filament Presentation Architecture use `-`.

#### 4. [Nit] Malformed LaTeX Math Mode Syntax
* **Affected Scope:** Line 178 (`### 3. Financial Calculation Formulations`)
* **Discrepancy Description & Rationale:**
  The use of `\mathbin{??}` containing unescaped double question marks alongside unescaped single quotes (`'exponit.default_visit_cost'`) inside raw LaTeX math mode triggers rendering artifacts and parser warnings in standard KaTeX and GitHub-flavored Markdown renderers.

#### 5. [Nit] Unlinked File Paths, Code Symbols, and Console Routes
* **Affected Scope:** Lines 6, 12, 39, 51, 77, 204, 261–284
* **Discrepancy Description & Rationale:**
  Key architectural entities (such as `DoctorPortfolioQueryService`, `PromotionalInputResource`, `doctor_revenues`, and `/console/doctor-portfolio`) are styled solely as raw text or inline code literals without clickable file or route links.

---

### C. Style Vector (`egm-style`)

#### 1. [Major] Unquantified and Subjective Tier Thresholds
* **Affected Scope:** Section `## Overview` (Lines 14–17) and Section `### 5. Filament v5 Presentation Architecture` (Line 284)
* **Style Rationale:** Imprecise and qualitative definitions. Framing "High-Yield Prescribers" as relationships where commercial return "significantly exceeds" investment, and "Balanced Prescribers" as those that "cover ongoing... costs", relies on subjective language without numerical boundaries.

#### 2. [Major] Hyperbolic Phrasing, Sensationalism, and Buzzwords in Alternatives
* **Affected Scope:** Section `## Alternatives Considered` (Lines 296, 298, 306, 310)
* **Style Rationale:** Exaggerated claims, dramatic rhetoric, and marketing terminology:
  * **Lines 296 & 298:** "Severe Cross-Stack Coupling" and "severe cross-system operational fragility" introduce dramatic emotional framing rather than factual engineering trade-offs.
  * **Line 298:** "significantly more robust" relies on "robust" as an unsubstantiated buzzword.
  * **Line 306:** "satisfy 100% of executive business requirements with zero redundant framework overhead" uses unprovable absolute claims ("100%", "zero redundant").
  * **Line 310:** "N+1 Query Explosion" uses sensationalized phrasing ("Explosion") instead of neutral technical terminology (e.g., "N+1 Query Overhead" or "Excessive Roundtrips").

#### 3. [Nit] Informal Slang and Jargon Acronyms
* **Affected Scope:** Section `## Alternatives Considered` (Lines 318, 320)
* **Style Rationale:** Use of developer slang ("Premature Complexity (YAGNI)" and "Reason for Rejection: YAGNI"). A formal engineering specification should state the direct technical rationale rather than colloquial catchphrases.

#### 4. [Nit] Metaphorical Idioms and Editorializing in Problem Statement
* **Affected Scope:** Section `## The Problem` (Lines 23, 26, 28)
* **Style Rationale:**
  * **Line 23:** "substantial capital" is vague and unquantified.
  * **Line 26:** "Expenses are tracked in a vacuum" is an informal idiomatic metaphor. Prefer factual phrasing: "Activity logs lack cost attribution."
  * **Line 28:** "Resource allocation is intuitive rather than data-driven" and "wasted sampling budgets" employ corporate cliché and editorial judgment instead of neutral problem statements.

#### 5. [Nit] Filler Adjectives and Vague Modifiers
* **Affected Scope:** Sections `## Goals`, `## Open Questions`, `## Critical User Journeys`, and `### 4. High-Performance SQLite Query Service` (Lines 38, 39, 44, 58, 68, 201)
* **Style Rationale:**
  * **Line 38:** "fast inline entry" uses the subjective, unmeasurable adjective "fast".
  * **Line 39:** "dedicated Filament v5 management page" uses "dedicated" as filler.
  * **Line 44:** "Mathematical Robustness" is buzzword-heavy; the objective requirement is "Division-by-Zero Prevention".
  * **Line 58:** "absolute historical immutability" includes "absolute" as an unnecessary intensifier.
  * **Line 68:** "heavy sample consumption" relies on colloquial phrasing ("heavy"). Prefer "high volume".
  * **Line 201:** "High-Performance SQLite Query Service" uses "High-Performance" as an unbenchmarked promotional modifier. Prefer "SQLite Query Service".
