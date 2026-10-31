# Models Component Package

## Overview
Eloquent data models mapped to the SQLite database schema, defining domain entities, relationships, casts, and business query scopes.

## Interface
- **Core Catalog & CMS**:
  - `Product`: Pharmaceutical formulations (`slug`, `composition`, `packing`, `indications`). Belongs to `TherapeuticArea`. Has many `ProductPricing`.
  - `TherapeuticArea`: Medical specialties (`name`, `slug`, `description`, `icon`, `is_active`). Has many `Product`.
  - `NewsPost`: Press releases and clinical updates (`title`, `slug`, `content`, `published_at`).
  - `ContactSubmission`: Web lead submissions with spam scoring and handled status.
- **Field Force & MR Operations**:
  - `Doctor`: Healthcare provider registry with GPS coordinates, phone, area, and headquarters assignment.
  - `DCR`: Daily Call Report entries logging MR visits to doctors. Has many `DCRProduct` and `DCRPromotionalInput`.
  - `DCRProduct`: Products detailed during a specific MR visit.
  - `DCRPromotionalInput`: Promotional collateral/samples delivered during a visit.
  - `PromotionalInput`: Master list of promotional inputs and marketing materials.
  - `Area` / `Headquarter`: Hierarchical geographic sales territories.
  - `ProductPricing`: Price points including PTS, PTR, MRP, margin percentages, and GST.
- **Augmented Reality & Dynamic QR Telemetry**:
  - `ArCreative`: AR experiences with MindAR compiled `.mind` tracking files and 3D assets.
  - `QrCode`: Dynamic redirect QR entries with styling, access controls, and scan counters.
  - `QrScan`: Timestamped scan events capturing device, OS, browser, IP, and geolocation.
- **Doctor Microsites**:
  - `Microsite`: Custom landing pages accessible via `/dr/{slug}`.
  - `Showcase`: Clinic facilities, certifications, and equipment cards.
  - `Review`: Patient testimonials and ratings.
- **Authentication**:
  - `User`: System accounts and role permissions (Admin, Medical Representative).

## Invariants
- Foreign key constraints enforced across SQLite storage.
- Slugs for `Product`, `TherapeuticArea`, `NewsPost`, and `Microsite` are unique and URL-safe.
- Deletions cascading where appropriate (e.g. `DCRProduct` cascades on `DCR` delete).

## Side Effects
- State changes persist directly to `database/database.sqlite`.
- Saving `Product` and `TherapeuticArea` fires registered model observers to regenerate static `llms.txt`.

## Verification
- Run: `php artisan test --compact`
