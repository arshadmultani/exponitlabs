# Consolidated Review Report: Centralized Indian Festival & Healthcare Observance Calendar Engine

* **Target Document:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md) (Lines 1–541)
* **Auditor Swarm:** 6 Specialized Goldfish Reviewers (Skeptical Architect, Naive Reader, Hostile Design Critic, Structural Layout Validator, Technical Copy Editor, Pragmatic Tech Lead)
* **Execution Preset:** Consolidated Report with All Review Rubrics (Uninterrupted Full Flow)
* **Evaluation Date:** 2026-11-03
* **Overall Swarm Verdict:** **REJECTED (Architectural & Implementation Remediation Required)**

---

## 1. Executive Summary

The Centralized Indian Festival & Healthcare Observance Calendar Engine establishes a commendable, legally sound strategy for doctor relationship management. By anchoring field outreach to an objective, authoritative calendar rather than sensitive religious or demographic profiling, the design complies with India's Digital Personal Data Protection (DPDP) Act while avoiding operational rep friction.

The high-altitude architecture embraces key virtues:
* **Zero NPM Calendar Bloat**: Leveraging a native Livewire 4 / Tailwind CSS `grid-cols-7` month grid instead of heavy third-party calendar libraries.
* **Sync Invalidation Resolution**: Decoupling festival data from incremental `?since` queries and executing an atomic table replacement in IndexedDB.
* **Strict Timezone Anchoring**: Declaring Indian Standard Time (`Asia/Kolkata`, UTC+05:30) across all observance evaluations.

However, the 5-stage uninterrupted review swarm identified **critical blockers and implementation-readiness gaps** that would prevent clean execution:
1. **Existing Codebase Contract Disconnect**: The plan assumes the Call Reminder Studio (`mr-app.js`) and `celebration.js` canvas engine dynamically accept festival greeting copy. In reality, `celebration.js` has a hardcoded appreciation quote and `mr-app.js` unconditionally resets event state to default product gratitude copy.
2. **Destructive Dexie Schema Declaration**: The schema upgrade snippet re-declares the `doctors` table with primary key `id`, mutating Dexie's immutable primary key (`uuid`) and threatening offline client database corruption.
3. **Database Engine Syntax Trap**: The domain service specifies MySQL `DATE_SUB / INTERVAL` functions, which throw fatal syntax errors on the project's SQLite engine.
4. **Client-Side Midnight Timezone Drift**: Evaluating `todayStr` via standard JavaScript `new Date()` defaults to UTC, causing greeting windows to open and close 5.5 hours late every night.
5. **Non-Existent Codebase File Paths**: Several inventory paths point to non-existent directories (`Api/V1/SyncController.php`, `resources/views/elos/`).
6. **Standard Compliance Omission**: Missing mandatory `## Risks & Mitigations` section.

---

## 2. Traceability Matrix

| Raw Finding Source | Issue Description | Consolidated Action Item | Priority |
| :--- | :--- | :--- | :---: |
| `egm-design`, `egm-structural` | Missing mandatory `## Risks & Mitigations` section | Action Item 1: Add Risks & Mitigations Section | 🔴 Blocker |
| `egm-zoom-out`, `egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details` | Phantom file paths (`Api/V1/SyncController.php`, `elos/dcr.blade.php`, `components/mr/doctor-profile.blade.php`) | Action Item 2: Correct File Inventory Paths | 🔴 Blocker |
| `egm-comprehension`, `egm-zoom-out` | Schema v6 re-declares `doctors` store, mutating primary key `uuid` -> `id` | Action Item 3: Correct Additive Dexie v6 Schema | 🔴 Blocker |
| `egm-zoom-out`, `egm-comprehension`, `egm-design`, `egm-implementation-readiness`, `egm-codebase-details` | `celebration.js` hardcodes plaque text; `mr-app.js` wipes custom greeting events; both omitted from inventory | Action Item 4: Patch Reminder Studio & Canvas Engine | 🔴 Blocker |
| `egm-comprehension`, `egm-implementation-readiness`, `egm-codebase-details` | MySQL `DATE_SUB / INTERVAL` syntax crashes SQLite engine | Action Item 5: Align Date Math with SQLite Engine | 🔴 Blocker |
| `egm-design`, `egm-comprehension` | Client `todayStr` defaults to UTC, causing 5.5-hour lag between 00:00 and 05:30 IST | Action Item 6: Deterministic Client-Side IST Date | 🔴 Blocker |
| `egm-design` | Separate `clear()` and `bulkPut()` calls in Dexie can wipe offline database on interruption | Action Item 7: Explicit Dexie Readwrite Transaction | 🟡 Major |
| `egm-design`, `egm-comprehension` | DCR top banner has no selected doctor; tapping studio modal fails | Action Item 8: Clarify DCR Observance Banner Context | 🟡 Major |
| `egm-design`, `egm-implementation-readiness` | `unique(['festival_id', 'year'])` fails on Islamic lunar double occurrences; sync drops late Dec dates on Jan 1 | Action Item 9: Relational Constraint & Sync Buffer | 🟡 Major |
| `egm-design`, `egm-comprehension`, `egm-implementation-readiness` | Specialty matching fails on `specialty = null` (100% of current DB) and case/stem differences | Action Item 10: Resilient Specialty Matching | 🟡 Major |
| `egm-design`, `egm-zoom-out` | Manual overrides have no rollback mechanism; standalone rollback architecture violates YAGNI | Action Item 11: Add "Reset to Baseline Date" Action | 🟡 Major |
| `egm-zoom-out`, `egm-implementation-readiness` | `ImportIndianFestivalsCommand` duplicates `FestivalSeeder` logic | Action Item 12: Consolidate Ingestion Logic | 🟡 Major |
| `egm-implementation-readiness`, `egm-codebase-details` | Missing model factories and invalid `livewire()` helper in Pest plan | Action Item 13: Add Model Factories & Livewire Tests | 🟡 Major |
| `egm-structural` | Malformed Mermaid subgraph syntax with unquoted spaces and brackets | Action Item 14: Fix Mermaid Diagram Syntax | 🟡 Major |
| `egm-style`, `egm-structural` | Enumeration mismatch: "three hurdles" introduces four items | Action Item 15: Fix Enumeration Count Mismatch | 🟡 Major |
| `egm-style` | Unprovable marketing claims and colloquial slang in headings ("JS Bloat", "Sliding Window Trap") | Action Item 16: Plain-English Copy Polish | 🟢 Nit |
| `egm-structural`, `egm-style` | UI CTA button copy variance ("Create Greeting", "Create Festive Card") | Action Item 17: Standardize CTA Button Copy | 🟢 Nit |
| `egm-design` | Uncached relational queries on high-frequency master data sync requests | Action Item 18: Cache Master Data Festival Payload | 🟢 Nit |

---

## 3. Prioritized Actionable Remediation Plan

### 🔴 Blockers (Must Fix Before Approval)

#### Action Item 1: Insert Mandatory `## Risks & Mitigations` Section
* **Files**: `docs/designs/festival_calendar.md`
* **Changes**: Add a formal `## Risks & Mitigations` section evaluating:
  1. *Lunisolar Moon Sighting Notification Latency*: Field reps in remote areas syncing late after sudden night-before crescent sightings. Mitigation: central admin instant push + "Reset to Baseline" fallback.
  2. *Device Clock Tampering & Skew*: Reps manually altering device time to trigger expired windows. Mitigation: deterministic IST formatting + server validation on call logging.
  3. *Client Storage Quota & IndexedDB Failures*: Storage quota exceeded during bulkPut. Mitigation: explicit Dexie readwrite transaction + non-empty array guards.
  4. *Clinical Specialty Taxonomy Drift*: Free-text doctor specialty strings failing to match festival tags. Mitigation: case-insensitive normalized matching + fallback display.

#### Action Item 2: Correct Phantom Filepaths Across Inventory & Implementation
* **Files**: `docs/designs/festival_calendar.md` (Lines 362–382, 477, 505, 508)
* **Changes**:
  - Replace `app/Http/Controllers/Api/V1/SyncController.php` with `app/Http/Controllers/Api/SyncController.php`.
  - Replace `resources/views/elos/dcr.blade.php` with `resources/views/mr/dcr.blade.php`.
  - Replace `resources/views/components/mr/doctor-profile.blade.php` with `resources/views/mr/doctors/show.blade.php`.

#### Action Item 3: Correct Dexie.js Schema Version 6 Declaration
* **Files**: `docs/designs/festival_calendar.md` (Lines 223–232, 499–500)
* **Changes**: Remove all re-declarations of existing stores (`doctors`, etc.) to prevent primary key mutation from `uuid` to `id`. Declare strictly the additive store:
  ```javascript
  db.version(6).stores({
      upcoming_festivals: 'id, category, date'
  });
  ```

#### Action Item 4: Patch Call Reminder Studio & Canvas Engine for Dynamic Greeting Copy
* **Files**: `docs/designs/festival_calendar.md` (Lines 362–382, 511–515)
* **Changes**:
  - Add `resources/js/mr-app.js` and `resources/js/reminder-cards/celebration.js` to the File Inventory.
  - In `resources/js/mr-app.js`: update `openWithDetail(detail)` to accept `detail.imageTemplate`, `detail.textTemplateId`, and `detail.greetingText`. If `detail.greetingText` is supplied, assign it directly to `this.messageText` and bypass `resetTemplateText()`. Ensure the event caller passes `detail.doctor` rather than `detail.doctorId`.
  - In `resources/js/reminder-cards/celebration.js`: update `drawCelebrationCard(ctx, W, H, data)` to render `data.greetingQuote || data.greetingMessage` if present, falling back to the existing static quote without introducing heavy text-wrapping abstractions.

#### Action Item 5: Align Date Math with SQLite Engine
* **Files**: `docs/designs/festival_calendar.md` (Lines 449–450)
* **Changes**: Eliminate MySQL-specific `DATE_SUB(...)` and `INTERVAL ... DAY` syntax. Specify SQLite-compatible syntax:
  ```sql
  date(festival_dates.date, '-' || festivals.days_before || ' days') <= ? 
  AND date(festival_dates.date, '+' || festivals.days_after || ' days') >= ?
  ```
  Or prescribe pre-loading eager records for the current year and evaluating window boundaries via Carbon in PHP: `$targetDate->between($windowStart, $windowEnd)`.

#### Action Item 6: Enforce Deterministic Client-Side IST Date Evaluation
* **Files**: `docs/designs/festival_calendar.md` (Lines 197–203, 240, 248)
* **Changes**: Explicitly prescribe the client-side IST helper to eliminate the 5.5-hour UTC midnight lag:
  ```javascript
  const todayStr = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata' }).format(new Date());
  ```

---

### 🟡 Majors (Required for Architecture & Stability)

#### Action Item 7: Atomic Dexie Table Replacement Transaction
* **Files**: `docs/designs/festival_calendar.md` (Lines 214–221, 501–503)
* **Changes**: Wrap the table clear-and-replace in an explicit Dexie readwrite transaction with a non-empty payload guard:
  ```javascript
  if (Array.isArray(data.festivals) && data.festivals.length > 0) {
      await db.transaction('rw', db.upcoming_festivals, async () => {
          await db.upcoming_festivals.clear();
          await db.upcoming_festivals.bulkPut(data.festivals);
      });
  }
  ```

#### Action Item 8: Clarify DCR Banner Doctor Selection Context
* **Files**: `docs/designs/festival_calendar.md` (Lines 68–69, 239–243, 505–507)
* **Changes**: Clarify that on `/elos/dcr`, because no doctor is selected on the initial visit form, the top banner serves an awareness function ("🪔 Active Observance: Diwali — Send greetings from doctor profiles") linking to `/elos/doctors`, while direct card generation occurs from individual doctor profiles (`/elos/doctors/{uuid}`).

#### Action Item 9: Relational Constraint & Sync Buffer Optimization
* **Files**: `docs/designs/festival_calendar.md` (Lines 140–154, 480–484)
* **Changes**:
  - Update table index from `unique(['festival_id', 'year'])` to `unique(['festival_id', 'date'])` to safely accommodate rare Islamic lunar double-occurrences in a single Gregorian year.
  - In `SyncController::masterData`, query `[$currentYear - 1, $currentYear, $currentYear + 1]` or use a sliding window (`whereBetween('date', [now('Asia/Kolkata')->subDays(15)->toDateString(), now('Asia/Kolkata')->addMonths(14)->toDateString()])`) so late December observances extending into early January are not cut off on January 1.

#### Action Item 10: Resilient Case-Insensitive Specialty Matching
* **Files**: `docs/designs/festival_calendar.md` (Lines 71–75, 129, 250–252, 451)
* **Changes**: Replace strict `Array.includes` with case-insensitive, normalized substring matching:
  ```javascript
  const docSpec = (currentDoctor.specialty || '').toLowerCase().trim();
  const isMatch = f.target_specialties.some(s => docSpec.includes(s.toLowerCase()) || s.toLowerCase().includes(docSpec));
  ```
  Define explicit fallback: if a doctor has `specialty = null` (as currently in production), clinical observances remain hidden unless explicitly tagged as universal.

#### Action Item 11: Add "Reset to Baseline Date" Action (Rejecting Over-Engineered Rollbacks)
* **Files**: `docs/designs/festival_calendar.md` (Lines 173–184, 467–470)
* **Changes**: Reject complex snapshot/event-sourcing rollback architectures per YAGNI. In `EditFestivalDateAction`, add a secondary button: **"Reset to Official Date"** that reads the default date from `database/data/indian_festivals.json`, updates `date`, and resets `is_manually_adjusted = false`.

#### Action Item 12: Consolidate Dataset Ingestion Logic
* **Files**: `docs/designs/festival_calendar.md` (Lines 368–369, 417–443)
* **Changes**: Avoid duplicate ingestion loops between `FestivalSeeder` and `ImportIndianFestivalsCommand`. Implement the import logic inside `ImportIndianFestivalsCommand` (supporting `--force`), and let `FestivalSeeder::run()` execute `$this->command->call('festivals:import');`.

#### Action Item 13: Add Model Factories & Livewire Test Conventions
* **Files**: `docs/designs/festival_calendar.md` (Lines 362–382, 518–535)
* **Changes**:
  - Add `database/factories/FestivalFactory.php` and `database/factories/FestivalDateFactory.php` to the File Inventory.
  - In `FestivalsCalendarTest.php`, specify testing using `Livewire\Livewire::test(FestivalsCalendar::class)` with `$this->actingAs(User::factory()->create())`.

#### Action Item 14: Fix Mermaid Subgraph Syntax
* **Files**: `docs/designs/festival_calendar.md` (Lines 85–112)
* **Changes**: Replace unquoted subgraphs (`subgraph Central Console [Filament v5]`, `subgraph Field Tier [/elos Mobile App]`) with standard alphanumeric identifiers and quoted labels (`subgraph CentralConsole ["Central Console (Filament v5)"]`).

#### Action Item 15: Fix Enumeration Count Mismatch
* **Files**: `docs/designs/festival_calendar.md` (Lines 21–26)
* **Changes**: Change "In the current workflow, reps face three distinct operational hurdles:" to "four distinct operational hurdles:".

---

### 🟢 Nits (Copy Polish & Optimization)

#### Action Item 16: Plain-English Copy Polish
* **Files**: `docs/designs/festival_calendar.md`
* **Changes**:
  - Replace unprovable marketing guarantees ("zero compliance risk", "guarantees 100% offline autonomy", "eliminates all cache invalidation bugs") with objective engineering statements.
  - Eliminate colloquial slang from headings ("JS Bloat", "Sliding Window Trap", "Ghost records").

#### Action Item 17: Standardize UI CTA Button Copy
* **Files**: `docs/designs/festival_calendar.md` (Lines 69, 257, 260, 510)
* **Changes**: Standardize the CTA copy uniformly to `"Create Greeting Card"`.

#### Action Item 18: Cache Master Data Festival Payload
* **Files**: `docs/designs/festival_calendar.md` (Lines 208–220, 480–498)
* **Changes**: Wrap the active festival JSON payload in `Cache::rememberForever('active_festivals_master_data', ...)` and invalidate it when a festival date is updated or imported.

---

## 4. Next Step Recommendation

The Goldfish review swarm has compiled a comprehensive, actionable audit. To proceed with remediating the specification:

```text
/egm-resolve docs/designs/festival_calendar.md
```
