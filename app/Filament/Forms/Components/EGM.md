# Filament Form Components Component Package

## Overview
Custom Livewire/Alpine form fields extending Filament forms for spatial geolocation, interactive map markers, and coordinate capture.

## Interface
- `App\Filament\Forms\Components\LocationMapField`: Interactive map picker field for selecting latitude and longitude coordinates.
  - Binds to numeric coordinate pairs with live preview and Leaflet/OSM mapping.

## Invariants
- Stores decimal latitude and longitude within database boundaries.

## Verification
- Run: `php artisan test --compact`
