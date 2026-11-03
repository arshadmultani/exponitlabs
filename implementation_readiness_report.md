# Implementation Readiness Audit: `docs/designs/festival_calendar.md` (Lines 335–541)

* **Target Document:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md)
* **Audit Scope:** Lines 335–541 (Detailed Implementation Plan & Verification Suite)
* **Standards:** [`implementation-readiness-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/implementation-readiness-standard.md)
* **Reviewers:** Pragmatic Tech Lead (`egm-implementation-readiness`) & Codebase Details Auditor (`egm-codebase-details`)
* **Verdict:** REJECTED (Changes Required Before Execution)

---

## Executive Summary

The implementation plan provides strong architectural concepts, but contains **4 🔴 Blockers**, **3 🟡 Majors**, and **1 🟢 Nit** when evaluated against the concrete codebase:
1. The plan cites phantom file paths from a previous directory structure (`app/Http/Controllers/Api/V1/SyncController.php`, `resources/views/elos/dcr.blade.php`, `resources/views/components/mr/doctor-profile.blade.php`).
2. It specifies MySQL-only date arithmetic (`DATE_SUB`, `DATE_ADD`, `INTERVAL`) on an application running exclusively on SQLite.
3. It breaks the Zero-Decision Rule by failing to account for breaking signature mismatches in `resources/js/mr-app.js`'s `callReminderApp`, and omits `mr-app.js` and `celebration.js` from the File Inventory.
4. It omits model factories and uses uninstalled Livewire test functions in the Pest suite.

---

## Detailed Audit Findings

### Finding 1: Phantom Controller Filepath and Namespace
* **Severity**: 🔴 Blocker
* **Affected Scope**: Lines 346, 373, 477
* **Discrepancy Description & Rationale**:
  Line 373 lists `app/Http/Controllers/Api/V1/SyncController.php` for modification. No such directory (`Api/V1`) exists in the project.
  The actual controller is located at `app/Http/Controllers/Api/SyncController.php` with namespace `App\Http\Controllers\Api`. The route prefix `/v1/sync` is configured in `routes/api.php` pointing directly to `App\Http\Controllers\Api\SyncController`.
* **Remediation**:
  Target `app/Http/Controllers/Api/SyncController.php`.

### Finding 2: Phantom Blade View Filepaths for Field App
* **Severity**: 🔴 Blocker
* **Affected Scope**: Lines 347, 376–377, 505–510
* **Discrepancy Description & Rationale**:
  The plan instructs modifying:
  1. `resources/views/elos/dcr.blade.php`
  2. `resources/views/components/mr/doctor-profile.blade.php`
  Neither file exists in the codebase (`resources/views/elos/` and `resources/views/components/mr/` do not exist).
  The actual codebase routes the `/elos` URL prefix to controllers in `app/Http/Controllers/MR/`, which render views under `resources/views/mr/`:
  - `elos.dcr` renders `resources/views/mr/dcr.blade.php`.
  - `elos.doctors.show` renders `resources/views/mr/doctors/show.blade.php`.
* **Remediation**:
  Target `resources/views/mr/dcr.blade.php` and `resources/views/mr/doctors/show.blade.php`.

### Finding 3: Unlisted Critical Files & Broken `openWithDetail` Contract
* **Severity**: 🔴 Blocker
* **Affected Scope**: Lines 362–382, 511–514 & `resources/js/mr-app.js`
* **Discrepancy Description & Rationale**:
  1. **Omission from File Inventory**: `resources/js/mr-app.js` and `resources/js/reminder-cards/celebration.js` are omitted from the File Inventory, yet Phase 5 is impossible to implement without modifying them.
  2. **Event Payload & Signature Mismatch**: Line 512 states `detail: { doctorId: ..., template: 'celebration', greetingText: ... }`. In `resources/js/mr-app.js`, `callReminderApp`'s `openWithDetail(detail)` handler reads `this.doctor = detail.doctor`. Passing `{ doctorId: ... }` leaves `this.doctor` null, breaking the studio modal. Furthermore, lines 1189–1190 unconditionally reset `this.selectedTextTemplateId = 'product_gratitude'` and wipe custom greeting text.
* **Remediation**:
  Add `resources/js/mr-app.js` and `resources/js/reminder-cards/celebration.js` to File Inventory. Update `openWithDetail(detail)` to accept `detail.imageTemplate`, `detail.textTemplateId`, and `detail.greetingText`.

### Finding 4: Incompatible MySQL Date Functions (`DATE_SUB`, `DATE_ADD`) on SQLite
* **Severity**: 🔴 Blocker
* **Affected Scope**: Line 450
* **Discrepancy Description & Rationale**:
  Line 450 specifies `DATE_SUB(festival_dates.date, INTERVAL festivals.days_before DAY) <= $targetDate AND DATE_ADD(...) >= $targetDate`.
  The application runtime and test suites run entirely on **SQLite**. `DATE_SUB()`, `DATE_ADD()`, and `INTERVAL ... DAY` are MySQL-specific functions that do not exist in SQLite, throwing `PDOException: no such function: DATE_SUB`.
* **Remediation**:
  Use SQLite-compatible syntax: `date(festival_dates.date, '-' || festivals.days_before || ' days') <= ? AND date(festival_dates.date, '+' || festivals.days_after || ' days') >= ?`, or perform evaluation in Carbon/Eloquent collection filtering.

### Finding 5: Master Data Sync Placement in `sync-engine.js`
* **Severity**: 🟡 Major
* **Affected Scope**: Lines 500–503 & `resources/js/sync-engine.js`
* **Discrepancy Description & Rationale**:
  In `resources/js/sync-engine.js`, full table clears occur strictly inside `if (!lastSync) { ... }`. Because festival definitions are decoupled from `?since` and always sent in full, the plan must explicitly specify that `await db.upcoming_festivals.clear()` and `await db.upcoming_festivals.bulkPut(data.festivals)` run **outside** the `if (!lastSync)` block. Placing it inside `if (!lastSync)` would cause incremental syncs to ignore admin festival date changes.
* **Remediation**:
  Explicitly place the atomic festival replacement outside `if (!lastSync)`.

### Finding 6: Missing Model Factories & Livewire Plugin Absence in Pest Plan
* **Severity**: 🟡 Major
* **Affected Scope**: Lines 362–382, 518–535
* **Discrepancy Description & Rationale**:
  1. The test suite relies on creating festival records. Model factories (`FestivalFactory.php`, `FestivalDateFactory.php`) are omitted from the inventory.
  2. `composer.json` has `pestphp/pest-plugin-laravel`, but does **not** have `pestphp/pest-plugin-livewire`. Tests must use `Livewire\Livewire::test(FestivalsCalendar::class)` instead of `livewire()`.
  3. Tests on `/console/festivals` must explicitly specify `$this->actingAs(User::factory()->create())`.
* **Remediation**:
  Add factories to the inventory and specify `Livewire::test(...)` with authenticated user setup.

### Finding 7: Year-Boundary Sync Query Truncation
* **Severity**: 🟡 Major
* **Affected Scope**: Lines 480–484
* **Discrepancy Description & Rationale**:
  Querying `whereIn('year', [$currentYear, $currentYear + 1])` causes late December observances with greeting windows extending into early January to be prematurely dropped upon January 1 rollover.
* **Remediation**:
  Query `[$currentYear - 1, $currentYear, $currentYear + 1]` or use a sliding date window.

### Finding 8: Hardcoded Celebration Plaque Text
* **Severity**: 🟢 Nit
* **Affected Scope**: Lines 347, 513 & `resources/js/reminder-cards/celebration.js`
* **Discrepancy Description & Rationale**:
  `celebration.js` hardcodes plaque text. If dynamic festival greetings are meant to appear on the 1080x1080 card itself, `celebration.js` must be updated to accept `data.greetingQuote`.
* **Remediation**:
  Update `celebration.js` to render `data.greetingQuote` if provided.
