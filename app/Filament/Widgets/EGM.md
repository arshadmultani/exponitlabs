# Filament Widgets Component Package

## Overview
Dashboard and table widgets rendering aggregate statistics, visual charts, and spatial mapping overlays for administrative users.

## Interface
- `App\Filament\Widgets\DoctorsMapWidget`: Interactive Leaflet map widget displaying geographic doctor locations, clustering markers by territory and area.

## Invariants
- Loads doctor coordinates efficiently with coordinate bounds filtering.

## Verification
- Run: `php artisan test --compact`
