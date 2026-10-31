# Providers Component Package

## Overview
Laravel service providers responsible for bootstrapping application services, configuring third-party packages, and registering administrative panels.

## Interface
- `App\Providers\AppServiceProvider`: Bootstraps core services, including raising Livewire temporary upload limits to 50MB for video and AR asset handling.
- **Subdirectories**:
  - [Filament](./Filament/EGM.md): Filament panel providers configuring the `/console` administration interface.

## Invariants
- `livewire.temporary_file_upload.rules` configured to 51,200 KB to match AR creative form upload limits.

## Side Effects
- Mutates application runtime configuration and registers framework bindings.

## Verification
- Run: `php artisan test --compact`
