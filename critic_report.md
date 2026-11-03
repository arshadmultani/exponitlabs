# Critic Review Report: Centralized Indian Festival & Healthcare Observance Calendar Engine

* **Target Document:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md) (Lines 1–334)
* **Standards:** [`feature-spec-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/feature-spec-standard.md) & Plain-English Copy Standard
* **Reviewers:** Hostile Design Critic (`egm-design`), Structural Layout Validator (`egm-structural`), Technical Copy Editor (`egm-style`)
* **Status:** Complete

---

## Executive Summary & Verdict

**Verdict: REJECTED (Requires Architectural & Structural Remediation)**

While the document demonstrates principled legal positioning regarding DPDP compliance and introduces sound offline table replacement concepts, it exhibits five critical flaws that require immediate correction before the plan can be considered production-ready:
1. **Standard Violation**: Omission of the mandatory `## Risks & Mitigations` section.
2. **Offline Data Loss Hazard**: Non-atomic table replacement in IndexedDB where `clear()` and `bulkPut()` execute in separate uncommitted transactions without a non-empty payload guard.
3. **Client-Side Midnight Timezone Drift**: Relying on undefined client-side date strings that default to UTC, causing a 5.5-hour lag every night in Indian Standard Time.
4. **Reminder Studio Integration Failure**: The canvas engine and client orchestrator (`mr-app.js`) currently have hardcoded text and overwrite custom event payloads.
5. **Relational & Operational Edge Cases**: Lunisolar double-occurrence collision with `unique(['festival_id', 'year'])`, lack of an administrative rollback mechanism for mistaken moon sighting overrides, and case-sensitive specialty matching where 100% of current doctor records have `specialty = null`.

---

## 1. Blocker Findings

### Finding 1.1: Standard Compliance Gap — Missing Mandatory "Risks & Mitigations" Section
* **Severity:** **Blocker**
* **Source:** `egm-design`, `egm-structural`
* **Affected Scope:** Lines 1–334
* **Standard Violation:** `feature-spec-standard.md`
* **Technical Description:** The specification completely omits the mandatory top-level `## Risks & Mitigations` section. There is no structured risk register assessing operational failure modes (mistaken moon sighting overrides, device clock drift, network partition timeouts during sync), security boundaries (unauthorized date edits), or disaster recovery/rollback plans.
* **Remediation Requirement:** Add a dedicated `## Risks & Mitigations` section following the Technical Plan.

### Finding 1.2: Non-Atomic IndexedDB Table Replacement (`clear() + bulkPut()`) Data Loss Hazard
* **Severity:** **Blocker**
* **Source:** `egm-design`
* **Affected Scope:** Lines 214–221, Lines 104–106, Alternative 3 (lines 304–306)
* **Technical Description:** Lines 217–220 propose:
  ```javascript
  if (data.festivals && Array.isArray(data.festivals)) {
      await db.upcoming_festivals.clear();
      await db.upcoming_festivals.bulkPut(data.festivals);
  }
  ```
  In Dexie.js, separate `await clear()` and `await bulkPut()` execute in two distinct auto-committed transactions. If the browser tab closes, network sync is interrupted, device battery dies, or `bulkPut()` throws a quota error between the calls, the local database is wiped clean, destroying offline calendar data. Furthermore, if the server returns `[]`, `clear()` empties local data permanently.
* **Remediation Requirement:** Wrap the operation in an explicit readwrite transaction with a non-empty payload guard:
  ```javascript
  if (data.festivals && Array.isArray(data.festivals) && data.festivals.length > 0) {
      await db.transaction('rw', db.upcoming_festivals, async () => {
          await db.upcoming_festivals.clear();
          await db.upcoming_festivals.bulkPut(data.festivals);
      });
  }
  ```

### Finding 1.3: Client-Side Midnight Timezone Invariant Trap
* **Severity:** **Blocker**
* **Source:** `egm-design`, `egm-comprehension`
* **Affected Scope:** Lines 186–203, Lines 240, 246–253
* **Technical Description:** While the backend declares `Asia/Kolkata`, client-side JavaScript date evaluation uses an undefined `todayStr`. Standard `new Date().toISOString().slice(0, 10)` generates a UTC date string. Between 00:00:00 and 05:30:00 IST every night, UTC is on yesterday's date, causing greeting windows to open or close 5.5 hours late on field mobile devices.
* **Remediation Requirement:** Specify the deterministic client-side IST date formatting helper:
  ```javascript
  const todayStr = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata' }).format(new Date());
  ```

### Finding 1.4: Reminder Studio & Canvas Dynamic Copy Disconnect
* **Severity:** **Blocker**
* **Source:** `egm-design`, `egm-zoom-out`, `egm-comprehension`
* **Affected Scope:** Lines 260–266, Lines 44–46, Lines 108–110
* **Technical Description:**
  1. In `resources/js/mr-app.js` (lines 1071–1200), `openWithDetail()` hardcodes `this.selectedTextTemplateId = 'product_gratitude'` and ignores `detail.template`, `detail.cardTemplate`, and `detail.greetingText`.
  2. In `resources/js/reminder-cards/celebration.js` (lines 124–128), `drawCelebrationCard` has a hardcoded plaque quote and no parameter or routine for dynamic festival text.
  As currently specified, clicking "Create Greeting Card" opens a generic post-visit card with no mention of the festival.
* **Remediation Requirement:** Include `resources/js/mr-app.js` and `resources/js/reminder-cards/celebration.js` in the plan to accept and render dynamic `data.festivalTitle` and `data.customQuote`.

---

## 2. Major Findings

### Finding 2.1: Lunisolar Relational Constraint Collision & Year-Boundary Sync Cutoff
* **Severity:** **Major**
* **Source:** `egm-design`
* **Affected Scope:** Lines 140–154, 212–214, 480–484
* **Technical Description:**
  1. `unique(['festival_id', 'year'])` fails when an Islamic lunar festival occurs twice in one Gregorian year (roughly every 33 years due to the 354-day lunar calendar).
  2. `whereIn('year', [$currentYear, $currentYear + 1])` in `SyncController` abruptly drops late December festivals on January 1, while greeting windows extending into January 1–2 are still active.
* **Remediation Requirement:** Change the unique constraint to `unique(['festival_id', 'date'])` and update the sync query to include a rolling date buffer.

### Finding 2.2: Specialty Matching Fragility & Null Specialty Invisibility
* **Severity:** **Major**
* **Source:** `egm-design`, `egm-comprehension`
* **Affected Scope:** Lines 71–75, 129, 250–252
* **Technical Description:** In the current database, 100% of doctor records (254/254) have `specialty = null`. `target_specialties.includes(null)` evaluates to false, rendering clinical health days completely invisible. Furthermore, strict `Array.includes` fails on case variations ("Cardiologist" vs "Cardiology").
* **Remediation Requirement:** Implement case-insensitive keyword matching and an explicit fallback rule for unassigned (`null`) doctor specialties.

### Finding 2.3: Irreversible Manual Overrides & Lack of Rollback Tooling
* **Severity:** **Major**
* **Source:** `egm-design`
* **Affected Scope:** Lines 88–90, 146–148, 425–442
* **Technical Description:** Once an admin adjusts a date and sets `is_manually_adjusted = true`, it is permanently skipped by seeders. If an administrator makes an error, there is no UI action or CLI tool to revert back to the official DoPT gazetted date.
* **Remediation Requirement:** Add a "Revert to Official Gazetted Date" action in the Filament slide-over and a `--force-reset-overrides` option in the seeder/command.

### Finding 2.4: Malformed Mermaid Diagram Subgraphs
* **Severity:** **Major**
* **Source:** `egm-structural`
* **Affected Scope:** Lines 85–112
* **Technical Description:** Subgraphs use unquoted spaces and slashes (`subgraph Central Console [Filament v5]`, `subgraph Field Tier [/elos Mobile App]`), which triggers syntax errors in strict Markdown renderers.
* **Remediation Requirement:** Use alphanumeric IDs with quoted display strings (`subgraph CentralConsole ["Central Console (Filament v5)"]`).

### Finding 2.5: Enumeration Inconsistency & Structural Logic
* **Severity:** **Major**
* **Source:** `egm-style`, `egm-structural`
* **Affected Scope:** Lines 21–26
* **Technical Description:** "In the current workflow, reps face three distinct operational hurdles:" is followed by four numbered items.
* **Remediation Requirement:** Update introductory text to specify four operational hurdles.

### Finding 2.6: Schema Column & Parameter Nomenclature Drift
* **Severity:** **Major**
* **Source:** `egm-structural`
* **Affected Scope:** Lines 133, 230, 260–263, 512
* **Technical Description:** Database column is `greeting_message_template`, sync payload is `greeting_message`, and Phase 5 event payload switches to `{ doctorId, template, greetingText }`.
* **Remediation Requirement:** Harmonize event parameter and payload keys uniformly to `{ doctorId, doctorName, cardTemplate, greetingMessage }`.

---

## 3. Style & Copy Polish (Nits)

* **Severity:** Nit
* **Source:** `egm-style`
* **Remediations:**
  1. Replace promotional adjectives and marketing claims ("zero compliance risk", "guarantees 100% offline autonomy", "eliminates all bugs") with objective engineering statements.
  2. Eliminate colloquial slang from headings ("JS Bloat", "Sliding Window Trap", "Ghost records").
  3. Standardize CTA button copy to "Create Greeting Card".
  4. Fix Markdown table pipe padding on lines 133 and 147.
