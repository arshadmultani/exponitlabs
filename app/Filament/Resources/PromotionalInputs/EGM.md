# Promotional Inputs Resource Component Package

## Overview
Filament administration resource managing promotional collateral, clinical literature, doctor gift inputs, and sample kits allocated to field representatives.

## Interface
- `App\Filament\Resources\PromotionalInputs\PromotionalInputResource`: Master resource for `PromotionalInput` model.
- **Pages**: `ListPromotionalInputs`, `CreatePromotionalInput`, `EditPromotionalInput`.

## Invariants
- Referenced by MR Daily Call Reports during field sample distribution tracking.

## Verification
- Run: `php artisan test --compact`
