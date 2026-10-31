# Console Commands Component Package

## Overview
Houses CLI maintenance, data pipeline, and operational artisan commands for backup automation, catalog compilation, and offline market analytics ingestion.

## Interface
- `App\Console\Commands\GenerateLlmsTxtCommand`: `php artisan seo:generate-llms {--dir=}`
  - Invokes `App\Support\LlmsTxt::saveToDisk()` to compile static `llms.txt` and `llms-full.txt` from active therapeutic areas, products, and news posts.
- `App\Console\Commands\SendBackupToTelegram`: `php artisan backup:telegram`
  - Zips SQLite database and public storage uploads into a timestamped archive and dispatches it to Telegram via Bot API.
- `App\Console\Commands\ImportInsights`: `php artisan insights:import {file} {--sheet=DATA}`
  - Streams monthly pharmaceutical sales-audit Excel workbooks via `OpenSpout` to construct an optimized read-only `insights.sqlite` analytics database with rollups.

## Invariants
- `seo:generate-llms` writes strictly UTF-8 plain text/markdown files to public path or specified directory.
- `backup:telegram` cleans up the local temporary zip archive after successful dispatch or on catch.
- `insights:import` executes locally with constant memory streaming, refusing to load raw multi-hundred-megabyte spreadsheets directly into memory.

## Side Effects
- File creation in `public/` and `storage/app/`.
- Network transmission to Telegram Bot API.
- Schema creation and writes to dedicated SQLite analytics database (`insights.sqlite`).

## Verification
- Run: `php artisan test --compact`
