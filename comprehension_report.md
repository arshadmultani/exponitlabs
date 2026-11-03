# Comprehension Goldfish Audit: Centralized Indian Festival & Healthcare Observance Calendar Engine

* **Target Document:** [`docs/designs/festival_calendar.md`](file:///Users/arm/Herd/exponitlabs/docs/designs/festival_calendar.md)
* **Evaluated Scope:** Lines 1–334 (Feature Specification) & Lines 335–541 (Detailed Implementation)
* **Standard:** [`re-implementation-standard.md`](file:///Users/arm/.gemini/config/plugins/egm/resources/document_standards/re-implementation-standard.md)
* **Auditor:** Naive Reader (`egm-comprehension`)
* **Status:** Complete

---

## Executive Summary

As a clean-room reader attempting to re-implement this feature from the specification alone, critical comprehension and architectural obstacles were identified:
1. It assumes the existing reminder studio and `celebration.js` canvas engine dynamically accept festival greeting copy, when in reality `celebration.js` hardcodes a generic doctor appreciation quote and `mr-app.js` unconditionally overwrites incoming events with default visit templates.
2. It provides a Dexie version upgrade snippet that alters the primary key of the core `doctors` table from `uuid` to `id`, which corrupts offline IndexedDB data.
3. Key file paths in the inventory are non-existent or misrouted.
4. The backend service specifies MySQL `DATE_SUB / INTERVAL` arithmetic on an application running SQLite.

---

## 1. Blocker Findings (Re-Implementation Obstacles)

### Finding 1.1: Disconnect with Existing Reminder Studio & `celebration.js` Engine
* **Severity**: Blocker
* **Affected Scope**: Lines 37, 45, 69, 109–110, 259–266, 347, 511–514, 541
* **Discrepancy Description & Rationale**:
  The document asserts that Phase 5 directly reuses `resources/js/reminder-cards/celebration.js` and `resources/js/reminder-cards/text-engine.js` without changes.
  Reading the actual implementation in `resources/js/reminder-cards/celebration.js` reveals that the plaque banner and quote are **hardcoded**:
  ```javascript
  ctx.fillText('WARMEST WISHES & SINCERE REGARDS', W / 2, plaqueY + 50);
  ctx.fillText('“Wishing you boundless health, joy, and continued distinction', W / 2, plaqueY + 105);
  ctx.fillText('in your tireless and noble dedication to healing lives.”', W / 2, plaqueY + 140);
  ```
  Neither `celebration.js` nor `renderReminderCanvas` in `reminder-cards/index.js` accepts `greetingMessage`, `customQuote`, or `festivalName`.
  Furthermore, in `resources/js/mr-app.js`, `openWithDetail()` **unconditionally resets** the text template:
  ```javascript
  this.selectedTextTemplateId = 'product_gratitude';
  this.resetTemplateText();
  ```
  It completely ignores any `detail.template` or `detail.greetingText` passed by custom events, leaving the image template at `'executive'`.
  Neither `resources/js/mr-app.js`, `resources/js/reminder-cards/celebration.js`, nor `resources/js/reminder-cards/index.js` is listed in the File Inventory (lines 360–382).

### Finding 1.2: Destructive Dexie.js Schema Declaration Corrupting Existing Stores
* **Severity**: Blocker
* **Affected Scope**: Lines 223–232 and Lines 499–500
* **Discrepancy Description & Rationale**:
  Line 228 instructs updating `resources/js/offline-db.js`:
  ```javascript
  db.version(6).stores({
      // Preserved existing stores...
      doctors: 'id, name, status, area_id, doctor_potential_id',
      upcoming_festivals: 'id, festival_id, category, date, window_start, window_end'
  });
  ```
  In the codebase (`resources/js/offline-db.js`), the `doctors` table schema is:
  `doctors: 'uuid, id, name, specialty, town, area_id, sync_status'` where **`uuid`** is the primary key.
  In Dexie.js, the first key listed is the primary key. Declaring `doctors: 'id, ...'` in version 6 mutates the primary key index from `uuid` to `id`, dropping records without numeric IDs or corrupting existing offline records. In Dexie, unmodified stores should not be redeclared. The declaration should strictly declare only the new store:
  ```javascript
  db.version(6).stores({
      upcoming_festivals: 'id, festival_id, category, date, window_start, window_end'
  });
  ```

### Finding 1.3: Invalid File Paths in Implementation Inventory
* **Severity**: Blocker
* **Affected Scope**: Lines 362–382 and Lines 505–510
* **Discrepancy Description & Rationale**:
  Three critical files targeted for modification do not exist at the paths given in the document:
  1. `app/Http/Controllers/Api/V1/SyncController.php` (line 373) -> The actual controller is `app/Http/Controllers/Api/SyncController.php`.
  2. `resources/views/elos/dcr.blade.php` (line 376) -> The actual view is `resources/views/mr/dcr.blade.php`.
  3. `resources/views/components/mr/doctor-profile.blade.php` (line 377) -> The actual view is `resources/views/mr/doctors/show.blade.php`.

### Finding 1.4: Incompatible Raw MySQL SQL on SQLite Engine
* **Severity**: Blocker
* **Affected Scope**: Lines 449–450
* **Discrepancy Description & Rationale**:
  Line 450 prescribes evaluating active windows via:
  ```sql
  DATE_SUB(festival_dates.date, INTERVAL festivals.days_before DAY) <= $targetDate
  AND DATE_ADD(festival_dates.date, INTERVAL festivals.days_after DAY) >= $targetDate
  ```
  The platform runs on **SQLite**. SQLite does not support `DATE_SUB`, `DATE_ADD`, or `INTERVAL ... DAY`. Executing this query will crash with a fatal `PDOException: near "INTERVAL": syntax error`.

---

## 2. Major Findings

### Finding 2.1: Missing Doctor State for DCR Banner
* **Severity**: Major
* **Affected Scope**: Lines 68–69, 239–242, 505–507
* **Discrepancy Description & Rationale**:
  On `/elos/dcr`, no doctor is selected initially. `callReminderApp` requires an active doctor and a valid 10-digit phone number. Tapping the banner without a selected doctor fails or renders placeholder data.

### Finding 2.2: Timezone Inconsistency in Client-Side Window Evaluation
* **Severity**: Major
* **Affected Scope**: Lines 197–202 vs 240, 248, 506
* **Discrepancy Description & Rationale**:
  Client-side `todayStr` is undefined. Using `new Date().toISOString().split('T')[0]` evaluates in UTC, causing a 5.5-hour delay every night between 00:00 and 05:30 IST. Must use `new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata' }).format(new Date())`.

### Finding 2.3: Brittle Specialty Matching Logic
* **Severity**: Major
* **Affected Scope**: Lines 71–75, 251–252, 451
* **Discrepancy Description & Rationale**:
  In the database, doctor specialties are nullable free-text strings. Case-sensitive `includes()` will fail on variations (e.g. "Cardiologist" vs "Cardiology"). Needs normalized case-insensitive keyword matching and a fallback for `null` specialties.

### Finding 2.4: Undefined Return Schema for Month Grid Data
* **Severity**: Major
* **Affected Scope**: Lines 452–455
* **Discrepancy Description & Rationale**:
  `getMonthGridData(int $year, int $month): array` fails to specify the shape of each element in the 42-cell matrix (keys, date formats, festival structure).

---

## 3. Nit Findings

1. **Missing `FestivalSeeder` Registration in `DatabaseSeeder.php`** (lines 417–443).
2. **Missing Eloquent `$casts` Specification** on models for dates and booleans.
3. **Undefined Display Priority for Overlapping Observances** when cultural and clinical days coincide.
