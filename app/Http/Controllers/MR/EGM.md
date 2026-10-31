# Medical Representative (MR) Field Controllers Component Package

## Overview
Serves mobile-first, lightweight web interfaces for field representatives under the `/mr` prefix, engineered for zero layout bloat, client-side caching, and offline operability.

## Interface
- `App\Http\Controllers\MR\MRAuthController`: Handles MR mobile login, authentication state, and session revocation.
- `App\Http\Controllers\MR\MRDcrController`: Serves the offline Daily Call Report entry interface and visit logging shell.
- `App\Http\Controllers\MR\MRDoctorController`: Provides the doctor directory view, doctor detail screens, and offline doctor onboarding forms.
- `App\Http\Controllers\MR\MRPresentationController`: Delivers the offline digital visual aid presenter and detailing screen.

## Invariants
- Operates outside the Filament panel to avoid layout weight and server-rendered component latency.
- Renders lightweight Blade shells configured with Alpine.js and Dexie.js for offline data persistence.

## Side Effects
- Mutates user session authentication states.

## Verification
- Run: `php artisan test --compact`
