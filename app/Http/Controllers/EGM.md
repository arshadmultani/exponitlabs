# HTTP Controllers Component Package

## Overview
Coordinates incoming web requests across public marketing pages, AR experiences, dynamic QR code routing, doctor microsites, and offline sync endpoints.

## Interface
- **Public & Marketing**:
  - `PageController`: Serves Home (`/`), About (`/about`), Products (`/products`, `/products/{slug}`), News (`/news`, `/news/{slug}`), Contact form submission (`/contact`), dynamic `sitemap.xml`, and clean Markdown twin responses for LLMs.
- **Augmented Reality & QR**:
  - `ArController`: Serves the camera-driven MindAR + Three.js viewer (`/ar/{creative}`), handles `.mind` asset streaming, and verifies creative publication states.
  - `QrRedirectController`: Decodes `/qr/{code}` requests, queues asynchronous telemetry tracking via `RecordQrScanJob`, and performs 302 redirects to destination targets.
- **Doctor Microsites & Analytics**:
  - `MicrositeController`: Renders doctor profile microsites (`/dr/{slug}`).
  - `InsightsController`: Serves the internal pharmaceutical sales audit analytics dashboard (`/insights`) backed by `insights.sqlite`.
- **Subdirectories**:
  - [Api](./Api/EGM.md): Offline dataset bootstrap and client mutation synchronization endpoints.
  - [MR](./MR/EGM.md): Mobile-first field app views for Daily Call Reports, doctor directories, and offline visual aids.

## Invariants
- Honeypot validation applied to public contact form submissions (`spatie/laravel-honeypot`).
- Only published AR creatives (`is_active = true`) are accessible to public viewers; inactive creatives return 404.
- Inactive QR codes redirect to fallback destinations or return 404.
- Dispatches QR telemetry capture to queues rather than blocking redirect responses.

## Side Effects
- Database record creation for `contact_submissions`.
- Queued job dispatching for `RecordQrScanJob`.
- Discord webhook notifications on new contact leads.

## Verification
- Run: `php artisan test --compact`
