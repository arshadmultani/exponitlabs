# Stage 6: Comprehension Test & Re-Implementation Report

**Target File:** `docs/designs/doctor_portfolio.md`  
**Target Standard:** `resources/document_standards/re-implementation-standard.md`  
**Reviewer:** `egm-comprehension` (Naive Reader Goldfish)  
**Date:** 2026-11-01  
**Overall Verdict:** **FAIL**

---

## Executive Summary
The design document establishes a clear business motivation and architectural structure, but fails the Re-Implementation Standard. A competent engineer implementing this specification directly will encounter a blocker database exception on table rendering (due to missing `net_pnl` and `total_investment` SQL selections in `getBaseQuery`), will be unable to implement `DoctorRevenueImportService` without guessing file headers and contracts, and will face mathematical distortions when filtering rolling date periods against monthly normalized revenues.

---

## Findings

### 1. [Blocker] Query Service Omits `total_investment` and `net_pnl` Subqueries (Crashes Default Filament Sort)
* **Affected Scope:** Section 4 (lines 201–255), Section 5 Table Columns (lines 281–283), Section 3.3 (line 359), Section 6 Automated Verification (line 411).
* **Discrepancy Description & Rationale:**
  The specification mandates that the Prescriber Ledger Table be sortable by `total_investment` and default sorted by `net_pnl DESC` (lines 283, 380), explicitly citing *"Native SQL sorting on net_pnl and total_revenue without N+1 query loops"* (line 411) and *"correlated SQL subqueries for visits_count, samples_cost, promos_cost, total_revenue, and net_pnl"* (line 359).
  However, in the concrete code implementation provided for `DoctorPortfolioQueryService::getBaseQuery()` (lines 214–254), only `visits_count`, `samples_cost`, `promos_cost`, and `total_revenue` are selected. Neither `visit_investment`, `total_investment`, nor `net_pnl` are selected as SQL subqueries or calculated expressions. Furthermore, the local variable `$defaultVisitCost` is declared on line 216 but never used in the query.
  If an engineer implements the service as written, Filament will issue an `ORDER BY net_pnl DESC` query, immediately throwing a SQLite exception (`SQLSTATE[HY000]: General error: 1 no such column: net_pnl`). The document fails to provide the SQL expression or wrapping required to make `total_investment` and `net_pnl` native sortable columns.

---

### 2. [Blocker] Complete Absence of CSV File Schema, Contract, and Error Semantics for `DoctorRevenueImportService`
* **Affected Scope:** Section 1 CUJ 2 (lines 70–74), Open Questions (lines 59–60), Section 3.3 (lines 364–366), Section 6 (lines 414–418).
* **Discrepancy Description & Rationale:**
  The document introduces `DoctorRevenueImportService` and a modal action "Import Revenue CSV" as core deliverables, but omits essential technical contracts:
  1. **Missing CSV Format:** The required CSV column headers are nowhere defined (e.g., `doctor_id, amount` vs `doctor_uuid, period_date, amount, notes`).
  2. **Period Ambiguity:** CUJ 2 states the user *"selects the October 2026 period"* in the modal, but the service contract does not specify whether `period_date` is supplied via modal form input or parsed per CSV row.
  3. **Unresolved Open Question:** Open Question 2 (*"Should batch revenue uploads match doctors strictly by doctor_id... or fuzzy matching"*) is left unanswered and never resolved in the Technical Plan.
  4. **Error Handling & Atomicity:** The spec fails to define transaction semantics: does an invalid doctor ID trigger an all-or-nothing rollback, or a partial import with a row-level error summary?
  5. **Missing Service Interface:** The method signatures, parameters, and return types for `DoctorRevenueImportService` are completely absent.
  Under the Re-Implementation Standard, an engineer cannot implement this service without guessing file formats and API contracts.

---

### 3. [Major] Undefined Mathematical Thresholds for Prescriber Classification Tiers
* **Affected Scope:** Overview (lines 14–17), Section 5 Table Columns (line 284).
* **Discrepancy Description & Rationale:**
  The document specifies a badge column `tier` with values: `High-Yield Prescriber` (Success), `Balanced` (Warning), `Under-Yielding` (Danger), and `Incubation` (Info if registered < 60 days).
  However, the tier classification rules rely entirely on subjective prose:
  - *High-Yield:* "Commercial return significantly exceeds invested detailing and sampling capital"
  - *Balanced:* "Prescriptions cover ongoing field force maintenance and sample costs"
  - *Under-Yielding:* "Expenses cross threshold limits without generating reciprocal prescription volume"
  No mathematical boundaries or cutoff formulas (e.g., `Net P&L > 0` vs `ROI % >= 50%`, or `spend > ₹X` with `0` revenue) are provided. Furthermore, for `Incubation`, it states *"registered < 60 days"*, but does not specify whether this evaluates against `doctors.created_at` or `doctors.practice_since`, nor whether Incubation takes precedence over financial performance. An engineer cannot implement deterministic badge logic from this specification.

---

### 4. [Major] State Synchronization Gap Between `DoctorPortfolioStatsWidget` and `DoctorPortfolio` Page
* **Affected Scope:** Section 5 (lines 261–274), Section 3.4 (lines 377–384).
* **Discrepancy Description & Rationale:**
  The design specifies 4 time-horizon tab filters on the page (`current_fy`, `trailing_90`, `last_month`, `all_time`) and 4 executive cards on `DoctorPortfolioStatsWidget`.
  In Filament v5 / Livewire, page header widgets do not automatically inherit or listen to table tab filter changes without explicit architectural wiring (such as widget query parameters, Livewire event listeners, or reactive state passing). The document provides no contract or mechanism explaining how the widget receives the active date range from the page.
  Additionally, the document does not specify whether the widget queries the database independently via aggregate queries or reuses `DoctorPortfolioQueryService`.

---

### 5. [Major] Temporal Alignment Mismatch: Rolling 90 Days vs. Monthly Normalized Revenue
* **Affected Scope:** Section 2 Migration 4 (line 144), Section 3 Formulations (lines 176–189), Section 4 Query Service (lines 224–251), Section 5 Filters (line 266).
* **Discrepancy Description & Rationale:**
  `doctor_revenues.period_date` is normalized to the first day of the month (`YYYY-MM-01`).
  However, `d_c_r_s.date` contains exact visit timestamps (`YYYY-MM-DD`).
  When applying the `trailing_90` filter (rolling 90 days from today), `$startDate` falls in the middle of a month (e.g., Nov 1 − 90 days = Aug 3).
  In `getBaseQuery()`:
  - DCRs and sample costs are queried with `d_c_r_s.date >= '2026-08-03'` (capturing 28 days of August expenses).
  - Doctor revenue is queried with `doctor_revenues.period_date >= '2026-08-03'`.
  Because August revenue is stored as `'2026-08-01'`, the entire August revenue for all doctors is dropped (`2026-08-01 < 2026-08-03`), creating a severe artificial deficit where 90 days of costs are matched against only 60 days of revenue. The document fails to specify date alignment/snapping rules for monthly revenue versus daily operational logs.

---

### 6. [Major] Missing Formulas and Edge Case Handling for Widget Metrics ("Return Multiple" and "Account Efficiency Ratio")
* **Affected Scope:** Goals (line 40), Section 3 (lines 174–198), Section 5 (lines 272–273), Section 3.4 (line 383).
* **Discrepancy Description & Rationale:**
  While Section 3 provides explicit LaTeX formulas for `Effective Visit Cost`, `Total Invested Capital`, `Net Commercial P&L`, and `ROI %`, it completely omits definitions and formulas for two prominent metrics displayed on the executive summary cards:
  1. **Overall Return Multiple** (lines 40, 272, 383): Is this `Attributed Revenue / Total Invested Capital`, and how is division-by-zero handled when investment is ₹0?
  2. **Account Efficiency Ratio** (line 273: *"% of active doctors generating net-positive margin"*): What is the exact formula, does it divide by total active doctors or total doctors in the system, and how does it guard against zero active doctors?
  Furthermore, `ROI %` is extensively defined in Section 3 with zero-division handling, but is never displayed in either the prescriber table columns or the widget cards.

---

### 7. [Major] Catalog Price Mutability Contradicts CUJ 3 and Lacks Historical Snapshotting
* **Affected Scope:** Open Questions (line 58), CUJ 3 (lines 75–79), Section 4 (lines 235, 244).
* **Discrepancy Description & Rationale:**
  CUJ 3 states: *"When the admin updates the unit cost on PromotionalInputResource... New DCR activities reflect the updated cost baselines immediately in future calculations."*
  However, the data model and query engine join directly to master catalog tables (`products.sample_cost` and `promotional_inputs.unit_cost`) without any cost snapshot or effective dating on `dcr_products` and `dcr_promotional_inputs`.
  Consequently, updating a product's sample cost or promotional kit's unit cost retroactively alters all historical financial reports and P&L statements across previous quarters.
  While Open Question 1 acknowledges this trade-off (*"Snapshot vs. Dynamic Historical Accounting"*), it claims the decision is *"(Documented in Technical Plan)"*, but the Technical Plan contains no rationale, boundary, or explanation of this retroactive recalculation behavior.

---

### 8. [Nit] Undefined and Unlinked External Concepts & Acronyms
* **Affected Scope:** Overview (line 14), Alternatives (lines 293–298, line 304).
* **Discrepancy Description & Rationale:**
  1. `UCPMP` (line 304): Referenced without definition or documentation link. A new developer unfamiliar with Indian pharmaceutical compliance (Uniform Code for Pharmaceutical Marketing Practices) will have no context for why Wall Street terminology violates compliance norms.
  2. `ERPNext` / `Frappe` (lines 50, 146, 293–298): Mentioned as an external system without defining its role or providing a link to documentation for context.
  3. Acronyms `DCR` and `MR`: `DCR` is expanded as "detailing calls (DCRs)" on line 14 and "Daily Call Reports (`d_c_r_s`)" on line 26; standardizing to "Daily Call Report (DCR)" upon first mention improves clarity.

---

### 9. [Nit] Redundant Index in Migration 4 Schema
* **Affected Scope:** Section 2 Migration 4 (lines 151–152).
* **Discrepancy Description & Rationale:**
  In Migration 4, line 151 defines `$table->unique(['doctor_id', 'period_date'], 'unique_doctor_period_revenue');` and line 152 defines `$table->index(['doctor_id', 'period_date']);`.
  The composite unique constraint already creates a B-tree index on `['doctor_id', 'period_date']` in SQLite and MySQL. The additional non-unique index on the exact same column pair is redundant.

---

### 10. [Nit] Unclear File Origin for `config/exponit.php` and Conflicting Terminology in `DoctorRevenue`
* **Affected Scope:** Section 3.2 (lines 343–344, 352).
* **Discrepancy Description & Rationale:**
  1. `config/exponit.php` does not exist in the repository today. Section 3.2 lists it without specifying whether it is a new file to be created.
  2. Line 352 refers to "minor-unit monetary formatting helpers" on `DoctorRevenue`, whereas Migration 4 creates `amount` as `decimal:2` (standard Rupees, not paise minor units).
