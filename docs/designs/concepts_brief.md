# Doctor Portfolio Management & Commercial ROI Tracker

* **Author:** EGM Ideation Swarm (Elephant + 6 Goldfish Reviewers)
* **Date:** 2026-11-01
* **Target:** Filament Administrative Console (`/console/doctor-portfolio`)

---

## Overview

A commercial return-on-investment (ROI) dashboard embedded inside the Filament v5 administrative panel (`/console`). It equips leadership and marketing managers with account-level visibility into capital deployed versus commercial return generated across all 233 healthcare providers in the Exponit Labs doctor registry.

The system aggregates operational cost drivers captured by Medical Representatives (MRs) during Daily Call Reports (DCRs)—including rep call visits, distributed drug samples, and promotional collateral—and evaluates them against monthly prescription revenue attributed to each doctor. The dashboard replaces speculative intuition with concrete commercial performance tiers, identifying high-yield prescribers, steady contributors, and under-yielding accounts requiring rep frequency reassessment.

---

## Analysis (Synthesized Swarm Findings)

The 6-lens Goldfish review swarm audited the feature concept against system architecture, codebase patterns, ergonomics, tech debt, and implementation safety:

### 1. Database Indexing & Query Architecture (SQLite Guardrails)
- **The Risk:** In the current SQLite schema, `d_c_r_s` lacks indexes on `(doctor_id, date)`, and pivot tables `dcr_products` and `dcr_promotional_inputs` lack foreign key indexes. Calculating dynamic sums and sorting by computed virtual columns (Net P&L and ROI %) in Filament table queries without indexes will trigger unindexed multi-table scans, causing request timeouts.
- **Resolution:** 
  1. Add composite indexes via migrations: `d_c_r_s(doctor_id, date)`, `dcr_products(dcr_id, product_id)`, and `dcr_promotional_inputs(dcr_id, promotional_input_id)`.
  2. Implement correlated SQL subqueries in `modifyQueryUsing` on the Filament query builder rather than PHP Eloquent accessors. This pushes sorting and pagination down to SQLite, eliminating N+1 queries.

### 2. Temporal Accounting Invariant (Cost Point-in-Time Immutability)
- **The Risk:** Multiplying historical quantities by live product master costs (`dcr_items.qty * products.sample_cost`) causes retrospective data corruption. If sample prices or gift costs update in 2027, all historical 2025 and 2026 financial metrics and quarterly reports will silently mutate.
- **Resolution:** 
  - For v1 pragmatic implementation: Catalog prices act as active standard cost baselines.
  - For long-term audit compliance: Migrations should add `unit_cost_snapshot` to line items or record costs at DCR closure to preserve historical immutability.

### 3. Pharma Domain Ergonomics vs. "Broker-Terminal" Gimmick
- **The Risk:** Labeling practicing physicians with Wall Street stock tickers, "gainers vs losers", and "capital drains" introduces high cognitive friction, clashes with Filament v5's design system, and presents severe regulatory compliance risks under pharmaceutical marketing codes (e.g., UCPMP in India) if screens are exported or audited.
- **Resolution:** Standardize on professional pharmaceutical commercial terminology:
  - Screen Title: **Doctor Commercial Performance & ROI Portfolio**
  - Tiers: **High-Yield Prescribers (Net +)**, **Balanced Accounts**, and **Under-Yielding Accounts (Net -)**.
  - UI Components: Native Filament v5 `StatsOverviewWidget` cards (Total Capital Deployed, Total Attributed Revenue, Net Commercial P&L, Overall Return Multiple) and standard Filament tables with badge columns.

### 4. Cadence Mismatch & Attribution Lag
- **The Risk:** DCR expenses accumulate daily in real time, while prescription revenue is booked retrospectively on a monthly cadence. Evaluating a doctor mid-month will falsely mark them as negative. Furthermore, doctor detailing has a 30–90 day prescription ramp.
- **Resolution:**
  - Time-horizon filters must prioritize closed accounting periods: **All Time**, **This Financial Year (Apr–Mar)**, **Last Closed Month**, **Trailing 90 Days**.
  - New doctors detailed for under 60 days receive an **Incubation** status badge to prevent premature disinvestment.

### 5. Mathematical Division-by-Zero Safeguards
- **The Risk:** Doctors with ₹0 investment but positive revenue (organic legacy prescribers) yield infinite/undefined ROI. Doctors with investment but ₹0 revenue yield -100% regardless of whether the spend was ₹200 or ₹20,000.
- **Resolution:**
  - Primary ranking and sorting must default to **Absolute Net P&L (₹)** (`Revenue - Investment`).
  - Calculate ROI % safely: When investment is zero, display as `Organic / No Direct Cost` (or 100% cap) rather than division-by-zero overflow.

---

## Goals vs. Non-Goals

### In-Scope Goals (v1)
* **Master Catalog Cost Enrichment:**
  - Add `unit_cost` (decimal:2) to `promotional_inputs`.
  - Add `sample_cost` (decimal:2) to `products` (defaulting to existing unit cost).
  - Add `visit_cost_override` (nullable decimal:2) to `doctors`, defaulting to a configurable system visit cost (₹250 default).
* **Revenue Ingestion Model (`doctor_revenues`):**
  - Create table: `id`, `doctor_id`, `period_date` (date/ISO `YYYY-MM-01`), `amount` (decimal:2), `notes`, `source` (`manual_entry` | `csv_import`), `created_by_user_id`, `timestamps`.
  - Enforce composite unique constraint on `(doctor_id, period_date)` to guarantee idempotent re-imports.
* **Filament Executive Dashboard (`/console/doctor-portfolio`):**
  - Header statistics cards: Total Investment, Total Attributed Revenue, Net Portfolio P&L, Overall Portfolio ROI %.
  - Filterable Table: Doctor name, specialty, territory/area, call count, promotional input spend, sample medicine spend, total invested capital, total revenue, net P&L, and ROI tier badge.
  - Time Horizon Filters: All Time, Current Financial Year (India April–March cycle), Trailing 90 Days, Last Closed Month.
  - Inline / Modal Revenue Recording Action: Fast entry modal to log or update monthly doctor revenue.

### Out-of-Scope Non-Goals (Postponed to v2)
* **Direct ERPNext Database Connection:** No live socket/query coupling to local MariaDB or Python bench. Revenue data remains native to Laravel; ERP sync will be handled via a decoupled API importer script later.
* **Mobile Field App (`/mr`) Exposure:** Field representatives will **not** see financial margins, sample costs, or doctor P&L on mobile devices.
* **Complex Multi-Attribute Stock Sparklines:** No bespoke client-side charting libraries or broker-terminal trading ribbons; all UI relies on standard Filament v5 and Tailwind CSS v4 components.

---

## Key Decisions & Staging for Next Steps

1. **Storage Decoupling:** Keep `doctor_revenues` inside Laravel's SQLite database to maintain instant subquery performance and transactional safety.
2. **Standard Filament Presentation:** Leverage native Filament v5 table actions, filters, and stats widgets instead of custom standalone SPA wrappers.
3. **Primary Sort Metric:** Default table ordering to **Absolute Net P&L (₹) DESC**, surfacing the most commercially profitable prescribers at the top and the largest cash drains at the bottom.
4. **Idempotent Ingestion:** Require unique `(doctor_id, period_date)` to ensure monthly CSV revenue updates overwrite or update cleanly without duplicating revenue.
