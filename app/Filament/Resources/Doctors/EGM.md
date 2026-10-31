# Doctors Resource Component Package

## Overview
Filament administration resource managing the master healthcare provider directory, clinic addresses, contact details, specialty classifications, and map locations.

## Interface
- `App\Filament\Resources\Doctors\DoctorResource`: Full CRUD resource for `Doctor` model.
- **Pages**: `ListDoctors`, `CreateDoctor`, `EditDoctor`.
- **Schemas & Tables**:
  - `DoctorForm`: Name, registration/qualification, specialty, contact details, area assignment, and `LocationMapField` coordinate picker.
  - `DoctorsTable`: Table schema with area/specialty filters, quick search, and location status indicators.
- **RelationManagers**:
  - `DCRsRelationManager`: Field visit history for this specific doctor.

## Invariants
- Unique phone numbers or registration credentials where enforced.
- Area and Headquarter relationships preserved on update.

## Verification
- Run: `php artisan test --compact`
