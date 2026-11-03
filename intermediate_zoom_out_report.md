# Stage 8 Review Report: Intermediate Zoom Out Review

* **Target Document:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md) (Full file, Lines 1–541)
* **Target Standard:** [`zoom-out-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/zoom-out-standard.md)
* **Reviewer:** Skeptical Architect (`egm-zoom-out`)
* **Review Date:** 2026-11-03
* **Overall Verdict:** PASS WITH RESERVATIONS

---

## 1. Executive Summary

The proposed Centralized Indian Festival & Healthcare Observance Calendar Engine establishes a disciplined, privacy-compliant, and offline-capable architecture. By anchoring outreach strictly to an objective calendar rather than religious/community profiling, the design protects against regulatory compliance risks (DPDP Act) and brand liability.

The core architecture correctly embraces simplicity:
- **Native Livewire 4 & Tailwind Grid:** Rejection of external npm/JS calendar bloat (FullCalendar) in favor of a native `grid-cols-7` Blade template.
- **Atomic Master Data Sync:** Decoupling festival payloads from `?since` delta filtering in `SyncController::masterData` directly resolves the sliding-window sync trap with a negligible payload footprint (<10 KB).
- **Timezone Invariant:** Explicitly anchoring window math to Indian Standard Time (`Asia/Kolkata`).

However, evaluating the full document alongside the emerging remediation recommendations reveals several critical architectural hazards: **premature rollback tooling complexity creep**, **IndexedDB corruption risks via incorrect Dexie schema declarations**, **code duplication between seeders and CLI commands**, and **uncalibrated canvas rendering expectations**.

---

## 2. Evaluation of Proposed Remediation Fixes

| Proposed Fix | Architectural Assessment | Skeptical Architect Verdict & Guardrail |
| :--- | :--- | :--- |
| **Adding Dynamic Greeting Copy to `celebration.js`** | **Essential & Minimal** | `celebration.js` currently hardcodes a static quote (lines 125–128). Allowing `data.greetingQuote || data.greetingMessage` with the existing text as fallback is clean (~5 lines). **Guardrail:** Strictly reject introducing speculative multi-line text wrapping or dynamic font scaling engines. Keep greeting strings short and bounded. |
| **Fixing Dexie Schema Upgrade (v6)** | **Essential Safety Fix** | Line 228 erroneously re-declares `doctors` with primary key `id` (breaking v4's `uuid` primary key). In Dexie, table primary keys are immutable across versions. **Guardrail:** Schema v6 must be strictly additive, declaring only `upcoming_festivals`. Remove all re-declarations of existing stores. |
| **Adding Rollback Tooling** | **Blocker (YAGNI Violation)** | Building dedicated rollback architecture (versioning tables, snapshot logs, temporal state, or audit revert commands) for 2–4 lunisolar date shifts per year is gross over-engineering. **Guardrail:** Reject standalone rollback subsystems. A simple "Reset to Baseline Date" button in the existing slide-over modal is completely sufficient. |
| **Harmonizing Event Parameters** | **Essential Contract Alignment** | The document currently specifies conflicting parameters (`cardTemplate` vs `template`, `doctorId` vs `doctor`, `greetingMessage` vs `greetingText`). Aligning directly with the existing `callReminderApp` state avoids broken dispatch events. |

---

## 3. Categorized Findings & Architectural Critique

### Finding 1: Standalone "Rollback Tooling" is a Serious YAGNI Violation
* **Severity:** Blocker
* **Affected Scope:** Section 3 (`EditFestivalDateAction`, Lines 173–184) & Remediation Proposals
* **Simplicity Rationale:**
  Proposing a dedicated "rollback tooling" architecture (e.g., event logs, revision tracking tables, rollback artisan commands, or snapshot restore mechanisms) for administrative festival date adjustments violates YAGNI.
  In the real world, lunisolar calendar shifts (such as Eid moon sightings or state gazette shifts) occur only 2 to 4 times a year across ~40 observances.
  1. An administrator can easily rectify an erroneous date change by clicking the date badge in `/console/festivals` and re-adjusting the date to the correct day.
  2. The table already tracks `is_manually_adjusted`, `adjusted_by_user_id`, and `adjustment_notes`.
  3. If an administrator needs to revert a shifted date back to the GoI/Rashtriya Panchang default, the simplest possible solution is an action button inside the existing slide-over modal: **"Reset to Baseline Date"**, which copies the original date from `database/data/indian_festivals.json` and resets `is_manually_adjusted = false`.
  4. For batch resets, `php artisan festivals:import --force` already exists.
  Any design proposing separate rollback services, audit snapshot tables, or temporal state models must be firmly rejected.

### Finding 2: Dexie Schema v6 Re-declaration Risks Client Database Corruption
* **Severity:** Major
* **Affected Scope:** Section 5 (Lines 225–233) & Phase 4.2 (Lines 499–500)
* **Simplicity Rationale:**
  In `docs/designs/festival_calendar.md`, the schema upgrade is specified as:
  ```javascript
  db.version(6).stores({
      // Preserved existing stores...
      doctors: 'id, name, status, area_id, doctor_potential_id',
      // New festival store:
      upcoming_festivals: 'id, festival_id, category, date, window_start, window_end'
  });
  ```
  In `resources/js/offline-db.js`, the `doctors` table was defined in version 4 with primary key `uuid` (`doctors: 'uuid, id, name, specialty...'`).
  In Dexie.js:
  1. Primary keys are **strictly immutable** across database versions. Declaring `doctors: 'id, ...'` attempts to change the primary key from `uuid` to `id`, which triggers unrecoverable Dexie `SchemaError` exceptions on existing field devices or drops all cached doctor data.
  2. Dexie schemas are **additive**: you only declare tables that are added or modified in that specific version. Existing tables are automatically preserved from previous versions without re-declaration.
  3. Specifying 6 indexed columns (`id, festival_id, category, date, window_start, window_end`) is premature optimization. The entire payload for 2 years is ~80 records, which Section 6 queries in-memory using `.filter()`.
  **Remediation:** Declare only the new store in version 6:
  ```javascript
  db.version(6).stores({
      upcoming_festivals: 'id, category, date'
  });
  ```

### Finding 3: Contract Drift in `open-call-reminder` Custom Event Dispatch
* **Severity:** Major
* **Affected Scope:** Section 6 (Lines 260–266) & Phase 5.3 (Lines 511–514)
* **Simplicity Rationale:**
  The design specifies contradictory event payloads (`cardTemplate` vs `template`, `doctorId` vs `doctor`, `greetingMessage` vs `greetingText`).
  In `resources/js/mr-app.js`, `callReminderApp` expects `detail.doctor` (a full object containing `uuid`, `name`, `phone`, `specialty`), sets image template via `selectedImageTemplate`, sets text via `messageText`, and hardcodes `this.selectedTextTemplateId = 'product_gratitude'`.
  **Remediation:** Standardize the event contract across both Blade and JavaScript:
  ```javascript
  window.dispatchEvent(new CustomEvent('open-call-reminder', {
      detail: {
          doctor: currentDoctor,
          imageTemplate: 'celebration',
          textTemplateId: 'festive_greetings',
          greetingQuote: observance.name + ' Greetings',
          greetingMessage: observance.greeting_message || null,
          includeDate: false
      }
  }));
  ```

### Finding 4: Ingestion Logic Duplication Between Seeder and Artisan Command
* **Severity:** Major
* **Affected Scope:** Section 1 (Lines 88–91), File Inventory (Lines 368–369), and Phase 1.3 (Lines 417–443)
* **Simplicity Rationale:**
  The plan specifies both `database/seeders/FestivalSeeder.php` and `app/Console/Commands/ImportIndianFestivalsCommand.php` as separate files implementing identical ingestion logic.
  **Remediation:** Implement the import logic inside `ImportIndianFestivalsCommand` (supporting `--force`), and let `FestivalSeeder::run()` execute `$this->command->call('festivals:import');`.

### Finding 5: Dynamic Canvas Greeting Copy Scope Control
* **Severity:** Major
* **Affected Scope:** Section 6 (Lines 261–266) & `resources/js/reminder-cards/celebration.js`
* **Simplicity Rationale:**
  The specification assumes that `celebration.js` can be reused with zero code changes. However, inspecting `celebration.js` reveals that the plaque text is hardcoded.
  Fixing this requires:
  1. Passing `greetingQuote` from `options` into `renderer(...)` in `reminder-cards/index.js`.
  2. In `celebration.js`, rendering `data.greetingQuote` if present, otherwise falling back to the default quote.
  **Simplicity Guardrail:** The card plaque has a fixed height (`plaqueH = 340`). Do NOT implement a complex dynamic text-wrapping engine. Limit dynamic quotes to two concise lines or truncate cleanly.

### Finding 6: Inaccurate Codebase File Paths in Inventory
* **Severity:** Nit
* **Affected Scope:** Section File Inventory (Lines 362–383)
* **Simplicity Rationale:**
  Correcting paths (`app/Http/Controllers/Api/SyncController.php`, `resources/views/mr/dcr.blade.php`, `resources/views/mr/doctors/show.blade.php`) ensures implementation proceeds against real codebase files without accidental orphaned copies.
