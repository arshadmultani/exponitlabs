# News Posts Resource Component Package

## Overview
Filament CMS resource managing press releases, product launch announcements, and medical updates published on the public marketing site.

## Interface
- `App\Filament\Resources\NewsPosts\NewsPostResource`: Full CMS resource for `NewsPost` model.
- **Pages**: `ListNewsPosts`, `CreateNewsPost`, `EditNewsPost`.
- **Schemas & Tables**:
  - `NewsPostForm`: Title, slug generation, hero cover image, publication datetime, and rich text article editor.
  - `NewsPostsTable`: Publication status filters, scheduled publication dates, and search.

## Invariants
- Enforces unique slug; draft posts (`published_at > now()` or null) hidden from public listing `/news`.

## Verification
- Run: `php artisan test --compact`
