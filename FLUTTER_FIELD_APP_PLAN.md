# Flutter Field App (e-Detailing, DCR & Offline Sync) Master Plan

> **Target Platform:** Tablets (Android & iPadOS) + Mobile Phones  
> **Location in Repo:** `/mobile`  
> **Backend:** Laravel 13 + Sanctum (`/api/v1/...`) + Filament Admin Panel  
> **Architecture:** Offline-First Clean Architecture (Repository Pattern + MVVM)

---

## 1. System Architecture & Tech Stack

```text
exponitlabs/ (Monorepo)
├── app/                  # Laravel 13 Backend, Filament Admin, APIs
├── routes/api.php        # Sync & Auth API endpoints
├── database/             # SQLite / MySQL schema, migrations, seeders
│
└── mobile/               # Native Flutter Application
    ├── lib/
    │   ├── core/         # SQLite (Drift), Dio (Sanctum), Network Sync Worker, Theme
    │   ├── data/         # Repositories & API DTOs
    │   └── features/
    │       ├── auth/        # Token auth, offline PIN/biometric unlock
    │       ├── visual_aid/  # 16:9 Aspect Ratio Stage, Telestrator, Slide Analytics
    │       ├── dcr/         # Offline DCR Form, Sample Tracking, Outbox Queue
    │       ├── doctors/     # Instant Search Directory, In-Field Doctor Creation
    │       ├── games/       # Interactive Mini-games (MOA match, Wheel, Quizzes)
    │       └── survey/      # Doctor Questionnaires & Clinical Feedback
    └── test/
```

### Core Flutter Dependencies
- **State Management & DI:** `flutter_riverpod` (v2.6+)
- **Local Database (Offline-First):** `drift` (type-safe SQLite with reactive queries) + `sqlite3_flutter_libs`
- **Networking:** `dio` with Sanctum bearer token interceptor + retry policy
- **Offline Connectivity:** `connectivity_plus` (detects online/offline transitions)
- **Drawing / Telestrator:** Flutter `CustomPainter` + `signature` (60/120 FPS hardware accelerated)
- **Navigation:** `go_router` (declarative routing with deep links)
- **Local File & Media Storage:** `path_provider` (permanent storage for slide graphics & PDFs)
- **Location & Hardware:** `geolocator` (doctor clinic GPS tagging)

---

## 2. Step-by-Step Implementation Roadmap

### Phase 1: Environment & Project Foundation
- [ ] **1.1 Flutter Scaffold Setup**
  - Initialize Flutter project inside `/mobile` targeting Android & iOS (`flutter create --org com.exponitlabs --platforms=android,ios mobile`).
  - Configure `analysis_options.yaml` with strict linting rules (`flutter_lints`).
- [ ] **1.2 Core Theme & Tablet Responsive Layout**
  - Define theme tokens matching Exponit Labs brand identity.
  - Implement responsive stage container supporting 16:9 widescreen canvas across all tablet aspect ratios.
- [ ] **1.3 Authentication & Session Management**
  - Sanctum token authentication via `/api/user` and login endpoints.
  - Secure offline credential storage (`flutter_secure_storage`) with quick PIN/Biometric unlock.

### Phase 2: Local-First Database & Sync Engine
- [ ] **2.1 Drift SQLite Database Schema**
  - Tables: `doctors`, `products`, `promotional_inputs`, `dcrs`, `dcr_products`, `dcr_inputs`, `slide_analytics`, `sync_outbox`.
  - Compile-time type safety with generated DAO queries.
- [ ] **2.2 Delta Downlink Sync**
  - Integrate with Laravel's `/api/v1/sync/master-data?since={timestamp}`.
  - Populate and update local SQLite records for assigned territory doctors, products, and input items.
- [ ] **2.3 Uplink Outbox Queue Worker**
  - Store locally created records with `sync_status = 'pending'`.
  - Batch uplink to `/api/v1/sync/doctors-batch` and `/api/v1/sync/dcr-batch`.
  - Automatic background sync when `connectivity_plus` detects internet access.

### Phase 3: Offline Doctor Directory & Instant Creation
- [ ] **3.1 High-Performance Doctor Search**
  - Sub-millisecond full-text search across 10,000+ local doctors (by name, specialty, clinic, town).
  - Filter by specialty, territory, and visiting schedule.
- [ ] **3.2 Doctor Profile & Visit History**
  - View doctor details, past DCRs, products sampled, and detailing engagement history offline.
- [ ] **3.3 In-Field Doctor Creation**
  - Offline creation modal generating local UUID (`Uuid().v4()`).
  - Instantly saved to local DB and outbox queue; immediately available for DCR selection without network.

### Phase 4: e-Detailing Visual Aid & Presentation Engine
- [ ] **4.1 16:9 Presentation Stage**
  - High-performance slide viewer rendering bundled and cached slides (`slide-01.jpg` to `slide-26.jpg`).
  - Smooth 60/120 FPS hardware-accelerated transitions and touch navigation.
- [ ] **4.2 Interactive Telestrator (Drawing & Highlighter)**
  - Zero-lag finger/stylus drawing canvas over clinical trial slides.
  - Color palette, pen thickness, highlighter mode, undo, and 1-tap clear.
- [ ] **4.3 Presenter HUD & Detailing Controls**
  - 1-Tap Brand Jump Drawer: Jump directly to any therapeutic area/brand.
  - Black Screen Focus Mode: Instant screen blanking to direct eye contact back to rep.
  - Chamber Personalization: Dynamic doctor name, degree, and clinic watermark on clinical slides.
- [ ] **4.4 Detailing Analytics Engine**
  - Precision stopwatch tracking seconds spent per slide.
  - Automatically aggregates detailing summary into the visit DCR.

### Phase 5: Offline Daily Call Report (DCR)
- [ ] **5.1 Fast DCR Entry Form**
  - Select doctor, visit type, call date, and time.
  - Multi-product sample selector with unit counts.
  - Promotional input giveaway selector.
  - Joint-working colleague selection and remarks.
- [ ] **5.2 Clinic Geo-Tagging & Digital Signature**
  - Capture GPS coordinates at clinic check-in (`geolocator`).
  - Digital stylus/finger signature pad for doctor sample acknowledgment.
- [ ] **5.3 1-Tap Save to Outbox**
  - Instant local write with zero loading spinner; queued for automated background sync.

### Phase 6: Interactive Mini-Games, Quizzes & Questionnaires
- [ ] **6.1 Doctor Engagement Mini-Games**
  - **MOA Matching Game:** Drag-and-drop mechanism of action to therapeutic indications.
  - **Spin-the-Wheel / Clinical Challenge:** Rapid-fire clinical case study questions.
  - **Dosage Calculator:** Interactive slider calculating custom patient dosages.
- [ ] **6.2 Dynamic Doctor Questionnaires / Surveys**
  - Dynamic survey engine driven by schema from Laravel (single choice, multi choice, Likert scale, text).
  - Offline questionnaire submission queued with DCR.

---

## 3. Tooling & Verification Workflow

### Code Verification
```bash
# In /mobile
flutter analyze
flutter test
```

### Backend Sync Verification
```bash
# In root
php artisan test --filter=SyncTest
```
