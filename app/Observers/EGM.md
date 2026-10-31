# Observers Component Package

## Overview
Model lifecycle event subscribers executing cross-cutting side effects, including real-time static catalog synchronization for search engines and LLM answer engines.

## Interface
- `App\Observers\ProductObserver`: Listens to `Product` model lifecycle events (`saved`, `deleted`, `restored`, `forceDeleted`).
  - Implements `ShouldHandleEventsAfterCommit` to ensure database state is finalized before disk updates.
- `App\Observers\TherapeuticAreaObserver`: Listens to `TherapeuticArea` lifecycle events (`saved`, `deleted`, `restored`, `forceDeleted`).

## Invariants
- `public static bool $enabledInTests = false`: Disabled by default during PHPUnit/Pest test suites to avoid clobbering production/public disk artifacts.
- Execution deferred until after database transactions commit.

## Side Effects
- Rewrites `public/llms.txt` and `public/llms-full.txt` via `App\Support\LlmsTxt::saveToDisk()`.

## Verification
- Run: `php artisan test --compact`
