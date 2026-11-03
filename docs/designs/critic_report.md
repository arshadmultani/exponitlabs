# Stage 7 Review Report: Critic Review

* **Target Document:** [`docs/designs/doctor_cadence.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/doctor_cadence.md)
* **Audited Scope:** FeatureRange (Lines 1–309)
* **Target Standard:** [`feature-spec-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/feature-spec-standard.md)
* **Reviewers:** Hostile Design Critic (`egm-design`), Structural Layout Validator (`egm-structural`), Technical Copy Editor (`egm-style`)
* **Review Date:** 2026-11-02
* **Overall Verdict:** FAIL

---

## 1. Executive Summary

Stage 7 evaluated the specification across three critical vectors: System Architecture & Risks, Document Structure & Formatting, and Editorial Precision. While the functional data model addresses the user's requirements, the review revealed three blocking flaws:
1. Complete omission of the mandatory **Risks & Limits / Risks & Mitigations** blueprint section.
2. An architectural query anti-pattern (`Doctor::fromSub()`) that forces SQLite to materialize an in-memory unindexed derived table, completely defeating the composite index on filters and doubling subquery execution during pagination counts.
3. A critical business logic flaw where the default "Active Cadence" tab filters by `days_since_last_visit <= 60`, which automatically purges severely neglected Core prescribers (>60 days overdue) and hides all 140 unvisited prospect accounts.

Editorial critique also identified unmeasured performance hyperbole ("sub-millisecond"), marketing puffery ("effortless"), and unescaped Mermaid operators.

---

## 2. Findings by Review Vector

### Vector A: System Architecture & Risk (Hostile Design Critic)

#### Finding 7.A.1: Standard Compliance Gap — Missing Mandatory "Risks & Mitigations" Section
* **Severity:** Blocker
* **Affected Scope:** Document Root / Target Standard Compliance
* **Critical Flaw:** `feature-spec-standard.md` mandates an explicit `Risks & Limits` / `Risks & Mitigations` section identifying potential failure modes, security implications, scalability limits, and mitigations. The specification lacks this section entirely.
* **Proposed Mitigation:** Insert an explicit `## Risks & Mitigations` section addressing SQLite write-lock contention during concurrent field sync, index degradation on derived table materialization, and timezone drift.

#### Finding 7.A.2: Architectural Flaw — Derived Table `fromSub()` Destroys SQLite Indexing & Subquery Pushdown
* **Severity:** Blocker
* **Affected Scope:** Section 3 (Lines 174–203) & Section 2.B (Lines 163–165)
* **Critical Flaw:** Wrapping an inner query containing correlated aggregate subqueries in `Doctor::query()->fromSub($innerQuery, 'doctors')` forces SQLite to fully materialize the inner derived table into an ephemeral, unindexed in-memory B-Tree.
  1. The composite index `idx_doctors_cadence_lookup` (`status`, `doctor_potential_id`, `area_id`) is rendered completely useless when Filament applies filters (`area_id`, `doctor_potential_id`) on the outer query because the outer query operates on the unindexed anonymous derived table, forcing full table scans.
  2. When Filament evaluates pagination counts (`$query->paginate()`), it wraps the derived query in `SELECT COUNT(*) FROM (...)`, forcing SQLite to evaluate correlated subqueries across all 233+ doctors twice on every request.
* **Proposed Mitigation:** Eliminate the `fromSub()` wrapper. Attach subqueries directly onto the base Eloquent query using `addSelect()`, `selectSub()`, and `selectRaw()`:
  ```php
  $query = Doctor::query()
      ->where('status', 'active')
      ->select('doctors.*')
      ->selectSub($totalVisitsSubquery, 'total_visits_count')
      ->selectSub($mtdVisitsSubquery, 'visits_this_month_count')
      ->selectSub($lastVisitSubquery, 'last_visit_date');
  ```

#### Finding 7.A.3: Business Logic Flaw — "Active Cadence" Tab Expels Neglected Core Accounts
* **Severity:** Blocker
* **Affected Scope:** Section 4.B (Lines 104, 216–219)
* **Critical Flaw:** Section 4.B defines the default "Active Cadence" tab as `days_since_last_visit <= 60`. If a high-value Core prescriber reaches 61 days without a visit, they are automatically purged from the default route planning view and swept into "Unresponsive Accounts", exacerbating the exact neglect the system was designed to eliminate. Furthermore, all 140 unvisited accounts (`last_visit_date IS NULL`) vanish from the default screen.
* **Proposed Mitigation:** Partition tabs strictly by tier:
  - **Active Cadence (Default)**: `doctor_potentials.code IN ('core', 'serious', 'nascent')`.
  - **Unresponsive Accounts**: `doctor_potentials.code = 'unresponsive'`.
  - **All Accounts**: Unfiltered.

#### Finding 7.A.4: Offline Sync Flaw — Console `logVisit` Bypasses UUID Generation and Risks Duplicate DCRs
* **Severity:** Major
* **Affected Scope:** Section 4.E (Lines 253–257) & Section 5 (Lines 261–267)
* **Critical Flaw:** Inserting directly into `d_c_r_s` via `logVisit` leaves `uuid = null`, breaking client-side reconciliation keys in Dexie.js. Lacking a composite unique index on `(doctor_id, date)`, simultaneous offline logging and console logging produces duplicate records for the same visit.
* **Proposed Mitigation:** Add a model boot hook to `DCR.php` ensuring `uuid` is always generated, and add an idempotent check in `logVisit` verifying whether a DCR already exists for `(doctor_id, date)`.

#### Finding 7.A.5: Data Contract Flaw — Mobile Sync Payload Missing Tier Metadata
* **Severity:** Major
* **Affected Scope:** Section 5 (Lines 263–264)
* **Critical Flaw:** The mobile app stores data locally in Dexie.js and does not have a `doctor_potentials` table. Sending raw integer ID `doctor_potential_id: 2` is useless offline.
* **Proposed Mitigation:** Denormalize tier metadata directly onto the doctor serialization payload (`code`, `name`, `color`, `target_visits_per_month`).

---

### Vector B: Structural Layout & Standards (Structural Layout Validator)

#### Finding 7.B.1: Mermaid Diagram Unescaped Operators & Chained Edge Syntax
* **Severity:** Major
* **Location:** Lines 99, 104 (`### 1. Architectural System Overview`)
* **Defect:** Line 104 contains raw `<` and `>` operators inside a node label (`• Active Cadence (<=60d)`), which can cause SVG parsing errors in strict Markdown parsers. Line 99 uses chained multi-node syntax across subgraphs (`D & DCR --> IQ --> OQ`).
* **Remediation:** Escape operators using HTML entities (`&le;`, `&gt;`) and decompose chained edges into discrete edge declarations.

#### Finding 7.B.2: Section 5 Structural Asymmetry
* **Severity:** Nit
* **Location:** Lines 261–268 (`### 5. Offline Sync & Backward Compatibility`)
* **Defect:** Decomposes architecture into bullet points rather than lettered H4 subheadings (`#### A. ...`, `#### B. ...`).
* **Remediation:** Convert bullet points into formal H4 subheadings.

#### Finding 7.B.3: Heading Nomenclature Blueprint Alignment
* **Severity:** Nit
* **Location:** Lines 18, 78
* **Defect:** Rename `## The Problem` to `## Problem Statement`, and `## Technical Plan (Architecture)` to `## Proposed Architecture`.

---

### Vector C: Technical Style & Precision (Technical Copy Editor)

#### Finding 7.C.1: Prohibited Marketing Buzzwords & Fluff
* **Severity:** Major
* **Location:** Lines 46, 296
* **Defect:** "effortless" (Line 296) and "zero-friction" (Line 46).
* **Replacement:** Replace "effortless, 2-click form experience" with "two-field form input structure"; replace "Zero-Friction Offline Compatibility" with "Offline Sync Compatibility".

#### Finding 7.C.2: Unmeasured Performance Hyperbole
* **Severity:** Major
* **Location:** Lines 45, 305
* **Defect:** "Sub-Millisecond SQLite Query Execution".
* **Replacement:** "Database-Level Query Optimization" / "low-latency execution".

#### Finding 7.C.3: Narrative Storytelling in Problem Statement
* **Severity:** Major
* **Location:** Lines 14, 20
* **Defect:** "mood-driven", "reps wake up each morning", "subjective habit".
* **Replacement:** Replace with objective technical framing: "ad-hoc daily visit planning", "Field representatives currently schedule visits ad hoc based on proximity or personal preference rather than territory priority or target call frequencies."
