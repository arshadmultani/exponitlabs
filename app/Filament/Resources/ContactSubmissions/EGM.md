# Contact Submissions Resource Component Package

## Overview
Filament administration inbox for viewing and managing inbound website contact inquiries, partner messages, and spam-scored lead forms.

## Interface
- `App\Filament\Resources\ContactSubmissions\ContactSubmissionResource`: Read/action resource for `ContactSubmission` model.
- **Pages**: `ListContactSubmissions`, `ViewContactSubmission`.
- **Schemas & Tables**:
  - `ContactSubmissionInfolist`: Detailed view of sender name, email, phone, message content, and submission timestamp.
  - `ContactSubmissionsTable`: Inbox view with unhandled message badge count and mark-as-handled actions.

## Invariants
- Submissions are read-only; records can be marked handled or deleted but not modified in content.

## Verification
- Run: `php artisan test --compact`
