# Users Resource Component Package

## Overview
Filament administration resource managing user accounts, authentication credentials, administrative roles, and field representative assignments.

## Interface
- `App\Filament\Resources\Users\UserResource`: CRUD resource for `User` model.
- **Pages**: `ListUsers`, `CreateUser`, `EditUser`.
- **Schemas & Tables**:
  - `UserForm`: Name, email, password, and role selector (Administrator, Medical Representative).
  - `UsersTable`: User table with search, role filters, and email verification status.

## Invariants
- Unique email constraint enforced across users table.
- Passwords hashed using bcrypt/Argon2.

## Verification
- Run: `php artisan test --compact`
