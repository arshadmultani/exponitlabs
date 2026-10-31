# Support Component Package

## Overview
Shared domain helpers, pure-PHP generators, pricing calculators, Markdown twins, and search/AI-answer-engine (AEO) structured data builders.

## Interface
- `App\Support\Seo`: Central builder for schema.org JSON-LD structured data.
  - Generates `Organization`, `WebSite`, `Product`/`Drug`, `BreadcrumbList`, `NewsArticle`, `FAQPage`, and `SiteNavigationElement` scripts.
- `App\Support\Qr`: Pure-PHP QR builder using `endroid/qr-code`.
  - `forCreative(ArCreative $creative, string $format = 'png'): ResultInterface`: Builds print-ready AR QR code.
  - `forQrCode(QrCodeModel $qrCode, string $format = 'png'): ResultInterface`: Generates dynamic QR code with custom colors, sizing, and error correction.
- `App\Support\LlmsTxt`: Compiles `public/llms.txt` and `public/llms-full.txt` from active database entities.
  - `generate(): string`, `generateFull(): string`, `saveToDisk(?string $dir = null): void`.
- `App\Support\MarkdownTwin`: Renders clean Markdown twin representations of public marketing pages.
- `App\Support\PricingCalculator`: Evaluates multi-tier distributor, stockist, and retail pricing margins, schemes, GST, and net profit structures.

## Invariants
- `Qr` runs offline in pure PHP without invoking third-party generation APIs.
- `Seo` relies on compile-time constants for therapeutic areas, social links, and brand FAQs to avoid redundant queries during page rendering.
- `LlmsTxt` includes only published and active records.

## Side Effects
- Local filesystem writes to `public/llms.txt` and `public/llms-full.txt` when invoked.

## Verification
- Run: `php artisan test --compact`
