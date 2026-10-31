# Filament Auth Pages Component Package

## Overview
Custom authentication and account management pages extending default Filament auth flows.

## Interface
- `App\Filament\Pages\Auth\EditProfile`: Custom profile editing page allowing users to update their name, email, and password.

## Invariants
- Validates email uniqueness and password complexity prior to saving.

## Side Effects
- Mutates the authenticated `User` record in SQLite storage.

## Verification
- Run: `php artisan test --compact`
