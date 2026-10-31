# AI Agents Component Package

## Overview
Provides specialized AI agent implementations built on `laravel/ai` for automated visual extraction and OCR handwriting transcription.

## Interface
- `App\Ai\Agents\DoctorNameExtractorAgent`: Transcribes doctor names from handwritten medical reminder card images using Gemini structured output.
  - `instructions(): Stringable|string`: Returns system instructions for handwriting transcription.
  - `schema(JsonSchema $schema): array`: Structured output definition returning `{name: ?string, confidence: float}`.

## Invariants
- Operates under Gemini provider (`#[Provider('gemini')]`, `#[Model('gemini-3.5-flash')]`).
- Omits prefixes like "Dr." or "Doctor", returning strictly the name or null when unreadable.
- Returns a confidence score between 0.0 and 1.0.

## Side Effects
- Dispatches multimodal inference calls to external Gemini AI API via `laravel/ai`.

## Verification
- Run: `php artisan test --compact`
