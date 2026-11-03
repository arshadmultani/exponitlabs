# Goldfish Annotated Review Report

**Target:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md) (`full file`, lines 1–541)  
**Status:** resolved (All 18 Propose Fix items accepted and applied)  
**Evaluated by:** EGM Review Swarm (`egm-zoom-out`, `egm-comprehension`, `egm-design`, `egm-structural`, `egm-style`, `egm-implementation-readiness`, `egm-codebase-details`)  
**Resolution Agent:** Exponit Elephant (`egm-resolve`)  
**Date:** 2026-11-03

## Prioritized Findings & AI Suggestions

### Issue 1: Standard Compliance Gap — Missing Mandatory "Risks & Mitigations" Section [`egm-design`, `egm-structural`] - Severity: High
* **Affected Scope:** Section Boundary between Line 332 and Line 335
* **Discrepancy Description:** The document completely omits a dedicated "Risks & Mitigations" (or "Risks & Limits") section, violating `feature-spec-standard.md`.
* **Rationale:** The specification must identify operational failure modes, security boundaries, and disaster recovery/rollback plans.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** A structured risk register is required by the project standard to evaluate lunisolar announcement latency, client clock tampering, storage quotas, and specialty taxonomy drift.
* ***Proposed Edit:***
  * Location: Insert before `## Alternatives Considered` (or between Technical Plan and Alternatives)
  * Change: Add a dedicated `## Risks & Mitigations` section addressing:
    1. Lunisolar Moon Sighting Notification Latency vs Remote Rep Offline Sync.
    2. Client Mobile Clock Tampering & Midnight Invariance.
    3. IndexedDB Quota Limits & Atomic Sync Recovery.
    4. Clinical Specialty Tag Taxonomy Drift.

---

### Issue 2: Phantom File Paths Across Inventory and Detailed Implementation [`egm-zoom-out`, `egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: High
* **Affected Scope:** Lines 346, 362–382, 477, 505–510
* **Discrepancy Description:** The plan targets files in non-existent directories: `app/Http/Controllers/Api/V1/SyncController.php`, `resources/views/elos/dcr.blade.php`, and `resources/views/components/mr/doctor-profile.blade.php`.
* **Rationale:** In the codebase, routes map to `App\Http\Controllers\Api\SyncController.php` (no `V1/` directory), and `/elos` views reside under `resources/views/mr/` (`dcr.blade.php` and `doctors/show.blade.php`).
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** An implementer following the plan will create duplicate/orphaned files or encounter file-not-found errors.
* ***Proposed Edit:</a>
  * Location: Table 2 (File Inventory) and Phase 4/5 implementation headings
  * Change: Replace phantom paths with:
    - `app/Http/Controllers/Api/SyncController.php`
    - `resources/views/mr/dcr.blade.php`
    - `resources/views/mr/doctors/show.blade.php`

---

### Issue 3: Destructive Dexie.js Schema Declaration That Corrupts Existing Stores [`egm-comprehension`, `egm-zoom-out`] - Severity: High
* **Affected Scope:** Lines 223–232 and Lines 499–500
* **Discrepancy Description:** Line 228 specifies `db.version(6).stores({ doctors: 'id, name, ...', upcoming_festivals: '...' })`. In `resources/js/offline-db.js`, `doctors` was defined in v4 with primary key `uuid`.
* **Rationale:** In Dexie.js, primary keys are strictly immutable across versions. Re-declaring `doctors: 'id, ...'` mutates the primary key index from `uuid` to `id`, triggering schema errors or dropping cached doctors. Existing stores should not be re-declared in additive version bumps.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Schema version 6 must be strictly additive to prevent client-side data corruption on field devices.
* ***Proposed Edit:***
  * Location: Section 5 (Lines 226–232) and Phase 4 (Lines 499–500)
  * Change: Update the Dexie upgrade declaration to strictly declare only the new store:
    ```javascript
    db.version(6).stores({
        upcoming_festivals: 'id, category, date'
    });
    ```

---

### Issue 4: Call Reminder Studio & Canvas Engine Dynamic Greeting Copy Disconnect [`egm-zoom-out`, `egm-comprehension`, `egm-design`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: High
* **Affected Scope:** Lines 37, 45, 69, 109–110, 259–266, 360–382, 511–515
* **Discrepancy Description:** The design asserts that `celebration.js` and `callReminderApp` seamlessly pre-fill festival greetings without code changes. In reality, `celebration.js` hardcodes the quote, and `mr-app.js`'s `openWithDetail()` unconditionally resets `this.selectedTextTemplateId = 'product_gratitude'` and ignores custom greeting parameters.
* **Rationale:** Clicking "Create Greeting Card" would produce a generic doctor appreciation card with zero festival text.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Both files must be added to the File Inventory and updated with bounded dynamic text handling.
* ***Proposed Edit:***
  * Location: Lines 360–382 (File Inventory), Section 6, and Phase 5
  * Change:
    1. Add `resources/js/mr-app.js` and `resources/js/reminder-cards/celebration.js` to File Inventory.
    2. Update `callReminderApp.openWithDetail(detail)` in `mr-app.js` to accept `detail.imageTemplate`, `detail.textTemplateId`, and `detail.greetingText`, bypassing `resetTemplateText()` when `detail.greetingText` is supplied.
    3. Update `drawCelebrationCard(ctx, W, H, data)` in `celebration.js` to render `data.greetingQuote || data.greetingMessage` if present, falling back to the existing static quote without over-engineering text-wrapping engines.

---

### Issue 5: Incompatible MySQL Date Functions (`DATE_SUB`, `DATE_ADD`) on SQLite Engine [`egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details`] - Severity: High
* **Affected Scope:** Lines 449–450 (Phase 2, `FestivalGreetingService.php`)
* **Discrepancy Description:** Line 450 prescribes evaluating active windows via `DATE_SUB(festival_dates.date, INTERVAL festivals.days_before DAY) <= $targetDate AND DATE_ADD(...)`.
* **Rationale:** Exponit Labs runs on SQLite, which does not support `DATE_SUB`, `DATE_ADD`, or `INTERVAL ... DAY`. Executing this throws `PDOException: no such function: DATE_SUB`.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Must prescribe SQLite-compatible date syntax or Carbon collection evaluation.
* ***Proposed Edit:***
  * Location: Phase 2 (Line 450)
  * Change: Replace with SQLite-compatible SQL:
    `date(festival_dates.date, '-' || festivals.days_before || ' days') <= ? AND date(festival_dates.date, '+' || festivals.days_after || ' days') >= ?`
    Or specify pre-loading eager records and filtering with Carbon in PHP: `$targetDate->between($windowStart, $windowEnd)`.

---

### Issue 6: Client-Side Midnight Timezone Evaluation Trap [`egm-design`, `egm-comprehension`] - Severity: High
* **Affected Scope:** Lines 186–203, Lines 240, 248
* **Discrepancy Description:** Client-side JavaScript evaluation uses an undefined `todayStr`. Standard `new Date().toISOString().slice(0, 10)` generates a UTC date string.
* **Rationale:** Between 00:00:00 and 05:30:00 IST every night, UTC is on yesterday's date, causing greeting windows to open or close 5.5 hours late on field mobile devices.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Enforcing Indian Standard Time on the client eliminates the 5.5-hour midnight rollover bug.
* ***Proposed Edit:***
  * Location: Section 4 (Lines 197–203) and Section 6
  * Change: Mandate the client-side IST helper:
    ```javascript
    const todayStr = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata' }).format(new Date());
    ```

---

### Issue 7: Non-Atomic IndexedDB Table Replacement Data Loss Hazard [`egm-design`] - Severity: Medium
* **Affected Scope:** Lines 214–221, Lines 104–106, Alternative 3 (lines 304–306)
* **Discrepancy Description:** `await db.upcoming_festivals.clear(); await db.upcoming_festivals.bulkPut(data.festivals);` runs in two separate transactions.
* **Rationale:** If the app closes, battery dies, or `bulkPut()` fails between calls, the local festival store is left empty.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** An explicit readwrite transaction with a non-empty payload guard ensures atomic replacement.
* ***Proposed Edit:***
  * Location: Section 5 (Lines 216–221) and Phase 4 (Lines 501–503)
  * Change: Wrap in an explicit transaction:
    ```javascript
    if (Array.isArray(data.festivals) && data.festivals.length > 0) {
        await db.transaction('rw', db.upcoming_festivals, async () => {
            await db.upcoming_festivals.clear();
            await db.upcoming_festivals.bulkPut(data.festivals);
        });
    }
    ```

---

### Issue 8: Global DCR Banner Lacks Selected Doctor Context [`egm-design`, `egm-comprehension`] - Severity: Medium
* **Affected Scope:** Lines 68–69, Lines 239–243, Lines 505–507
* **Discrepancy Description:** In `/elos/dcr`, no doctor is selected on the initial visit form. Tapping the banner cannot open the Call Reminder modal without a selected doctor.
* **Rationale:** The studio requires a valid doctor object to construct WhatsApp click-to-chat links and render doctor metadata.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Clarify that the DCR banner serves an awareness function linking to the doctor directory, while card creation originates from doctor profiles.
* ***Proposed Edit:***
  * Location: CUJ 2, Section 6, and Phase 5
  * Change: State that the top DCR banner acts as an awareness alert ("🪔 Diwali window active — Send greetings from doctor profiles") linking to `/elos/doctors`, while one-tap card generation originates on individual doctor profiles (`/elos/doctors/{uuid}`).

---

### Issue 9: Relational Constraint Collision & Year-Boundary Sync Cutoff [`egm-design`, `egm-implementation-readiness`] - Severity: Medium
* **Affected Scope:** Lines 140–154, Lines 480–484
* **Discrepancy Description:** `unique(['festival_id', 'year'])` fails when an Islamic lunar festival occurs twice in a Gregorian year (~every 33 years). Furthermore, querying `whereIn('year', [$currentYear, $currentYear + 1])` drops late-December dates on January 1 while their greeting windows are still active.
* **Rationale:** Database schema must support lunar calendar shifts, and sync queries must maintain a date buffer.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Indexing on date accommodates dual occurrences, and a rolling date query prevents premature window cutoff.
* ***Proposed Edit:***
  * Location: Table `festival_dates` Schema and Phase 4
  * Change:
    - Change index to `unique(['festival_id', 'date'])`.
    - Update sync query to `whereBetween('date', [now('Asia/Kolkata')->subDays(15)->toDateString(), now('Asia/Kolkata')->addMonths(14)->toDateString()])` (or `[$currentYear - 1, $currentYear, $currentYear + 1]`).

---

### Issue 10: Specialty Matching Fragility & Null Specialty Invisibility [`egm-design`, `egm-comprehension`, `egm-implementation-readiness`] - Severity: Medium
* **Affected Scope:** Lines 71–75, Line 129, Lines 250–252, Line 451
* **Discrepancy Description:** 100% of current doctor records have `specialty = null`. `target_specialties.includes(null)` evaluates to false. Furthermore, strict `Array.includes` fails on case variations ("Cardiologist" vs "Cardiology").
* **Rationale:** Clinical health days would be completely invisible in the field app.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Normalized case-insensitive matching and fallback rules ensure reliable behavior.
* ***Proposed Edit:***
  * Location: Section 6 and Phase 2
  * Change: Specify case-insensitive substring matching:
    ```javascript
    const docSpec = (currentDoctor.specialty || '').toLowerCase().trim();
    const isMatch = f.target_specialties.some(s => docSpec.includes(s.toLowerCase()) || s.toLowerCase().includes(docSpec));
    ```
    If `currentDoctor.specialty` is null, clinical health days remain hidden unless marked universal.

---

### Issue 11: Standalone Rollback Tooling Architecture Violates YAGNI [`egm-zoom-out`, `egm-design`] - Severity: Medium
* **Affected Scope:** Lines 173–184, Lines 467–470
* **Discrepancy Description:** Proposing complex rollback tooling (versioning tables, snapshot logs, temporal state) for 2–4 lunisolar shifts per year is over-engineered.
* **Rationale:** A simple, direct action in the existing slide-over modal solves the problem without new architectural layers.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Adopting the Skeptical Architect's minimal guardrail enforces simplicity and avoids complexity creep.
* ***Proposed Edit:***
  * Location: Section 3 and Phase 3
  * Change: In `EditFestivalDateAction`, add a secondary button **"Reset to Official Date"** that re-populates the canonical date from `database/data/indian_festivals.json` and resets `is_manually_adjusted = false`.

---

### Issue 12: Ingestion Logic Duplication Between Seeder & Command [`egm-zoom-out`, `egm-implementation-readiness`] - Severity: Medium
* **Affected Scope:** Lines 88–91, Lines 368–369, Lines 417–443
* **Discrepancy Description:** Creating duplicate ingestion loops in both `FestivalSeeder.php` and `ImportIndianFestivalsCommand.php` violates DRY.
* **Rationale:** Dataset parsing and override-checking logic should live in one place.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Implementing the import logic in `ImportIndianFestivalsCommand` and letting `FestivalSeeder::run()` call `$this->command->call('festivals:import');` eliminates duplication.
* ***Proposed Edit:***
  * Location: Phase 1 (Lines 417–443) and File Inventory
  * Change: Let `FestivalSeeder` execute the artisan import command.

---

### Issue 13: Missing Model Factories & Livewire Test Conventions in Pest Plan [`egm-implementation-readiness`, `egm-codebase-details`] - Severity: Medium
* **Affected Scope:** Lines 362–382, Lines 518–535
* **Discrepancy Description:** Model factories (`FestivalFactory`, `FestivalDateFactory`) are omitted from inventory. Furthermore, Pest tests cannot use `livewire()` because `pest-plugin-livewire` is not installed; tests must use `Livewire\Livewire::test(...)`.
* **Rationale:** Adherence to repository testing standards and installed package capabilities.
* ***AI Suggestion:*** PROPOSE FIX
* ***Justification:*** Add factories to inventory and align test syntax with repository conventions.
* ***Proposed Edit:</a>
  * Location: Table 2 (File Inventory) and Section 4 (Testing Plan)
  * Change: Add factories to inventory; specify `Livewire\Livewire::test(FestivalsCalendar::class)` with `$this->actingAs(User::factory()->create())`.

---

### Issue 14: Malformed Mermaid Subgraph Identifiers [`egm-structural`] - Severity: Medium
* **Affected Scope:** Lines 85–112
* **Discrepancy Description:** Subgraphs use unquoted spaces and slashes (`subgraph Central Console [Filament v5]`, `subgraph Field Tier [/elos Mobile App]`), breaking strict Markdown parsers.
* **Rationale:** Syntax compliance for reliable diagram rendering.
* ***AI Suggestion:*** PROPOSE FIX
* ***Proposed Edit:***
  * Location: Lines 85–112
  * Change: Replace with alphanumeric identifiers and quoted display names (`subgraph CentralConsole ["Central Console (Filament v5)"]`).

---

### Issue 15: Enumeration Count Mismatch in Problem Statement [`egm-style`, `egm-structural`] - Severity: Medium
* **Affected Scope:** Lines 21–26
* **Discrepancy Description:** "In the current workflow, reps face three distinct operational hurdles:" introduces four numbered items.
* **Rationale:** Internal factual consistency.
* ***AI Suggestion:*** PROPOSE FIX
* ***Proposed Edit:***
  * Location: Line 21
  * Change: Replace "three distinct operational hurdles" with "four operational constraints".

---

### Issue 16: Plain-English Copy Polish & Fluff Removal [`egm-style`] - Severity: Low
* **Affected Scope:** Lines 11, 44–48, 281, 290, 294, 303–305, 318, 330
* **Discrepancy Description:** Promotional adjectives ("zero compliance risk", "guarantees 100% offline autonomy", "eliminates all bugs") and colloquial slang ("JS Bloat", "Sliding Window Trap").
* **Rationale:** Strict Plain-English standard and objective technical framing.
* ***AI Suggestion:*** PROPOSE FIX
* ***Proposed Edit:***
  * Location: Overview, Non-Goals, and Alternatives Considered
  * Change: Replace promotional adjectives and colloquial headings with objective engineering phrasing per Style audit findings.

---

### Issue 17: Standardize UI CTA Button Copy [`egm-structural`, `egm-style`] - Severity: Low
* **Affected Scope:** Lines 69, 257, 260, 510
* **Discrepancy Description:** Button copy fluctuates between "Create Greeting", "Create Greeting Card", and "Create Festive Card".
* **Rationale:** UI consistency across documentation and codebase.
* ***AI Suggestion:*** PROPOSE FIX
* ***Proposed Edit:***
  * Location: CUJ 2, Section 6, and Phase 5
  * Change: Standardize copy uniformly to `"Create Greeting Card"`.

---

### Issue 18: Master Data Active Festival Payload Caching [`egm-design`] - Severity: Low
* **Affected Scope:** Lines 208–220, Lines 480–498
* **Discrepancy Description:** Querying database on every master data sync poll creates redundant database overhead for static multi-year data.
* **Rationale:** Server performance optimization.
* ***AI Suggestion:*** PROPOSE FIX
* ***Proposed Edit:***
  * Location: Phase 4 (`SyncController::masterData`)
  * Change: Wrap payload generation in `Cache::rememberForever('active_festivals_master_data', ...)` and invalidate on date update/import.
