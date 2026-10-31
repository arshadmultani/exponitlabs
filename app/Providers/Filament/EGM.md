# Filament Providers Component Package

## Overview
Configures and initializes Filament administrative panels, themes, discovery directories, and middleware pipelines.

## Interface
- `App\Providers\Filament\ConsolePanelProvider`: Defines the administrative CMS panel under `/console`.
  - Discovers resources under `app/Filament/Resources`.
  - Discovers pages under `app/Filament/Pages`.
  - Discovers widgets under `app/Filament/Widgets`.
  - Configures theme (`resources/css/filament/console/theme.css`), profile management, authentication guards, and brand styling.

## Invariants
- Operates under the `console` panel ID with SPA navigation enabled (`->spa()`).
- Database transactions enabled for all panel requests (`->databaseTransactions()`).
- Dark mode disabled; primary brand palette set to `Emerald`.

## Side Effects
- Mounts `/console` administrative routes and registers panel services in the application container.

## Verification
- Run: `php artisan test --compact`
