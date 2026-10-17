# Exponit Doctor Toys — Offline Quick Draw Feature
## Agent-Ready Feature Development Specification
### Version 1.0 — September 2026

---

## 0. Executive Summary

Build an **offline-first, tablet-optimized drawing-recognition game** inspired by Google Quick, Draw!, but do **not** depend on Google's website or a live API.

Working name:

> **Exponit Draw**

The user is a doctor interacting with a Moto Pad Pro 12.7" tablet and stylus during an MR visit.

The experience must feel like a polished premium interactive toy rather than a pharma visual aid.

Core loop:

1. Show a simple object to draw.
2. Start a short countdown.
3. Doctor draws with the stylus.
4. Local browser AI analyzes the drawing.
5. App continuously displays its guesses.
6. App reveals the final guess.
7. Show a short, playful result.
8. Offer replay / another challenge.
9. Optionally queue anonymous analytics locally for later synchronization.

### Non-negotiable requirement

After the app has been opened once while online and all required assets have been cached, the drawing game must continue working with:

- Wi-Fi OFF
- Mobile data OFF
- Airplane mode ON
- No server/API access
- No CDN access

The AI inference itself must run locally on the Moto Pad browser.

---

# 1. Product Objective

## Primary objective

Create a 30–90 second interaction that:

- immediately attracts a doctor's attention
- makes the doctor physically use the tablet/stylus
- creates curiosity
- produces a small surprise/dopamine moment
- is memorable
- is easy to replay
- subtly associates the experience with Exponit Labs
- does not feel like a conventional pharmaceutical promotion

## Secondary objectives

The architecture must become a reusable foundation for future "Doctor Toys":

- Quick Draw
- Guess the Object
- Memory Challenge
- Reaction Challenge
- Generative Drawing
- Clinical Mini-Lab
- Other browser-based interactive experiments

Do NOT build a giant monolithic game platform in v1.

Build a clean reusable shell around one excellent experience.

---

# 2. Product Philosophy

The app should follow this sequence:

> INTERRUPT → PARTICIPATE → SURPRISE → REPLAY → REMEMBER

Avoid:

- long educational content
- registration
- login
- forms
- unnecessary menus
- medical terminology during the game
- forced product messaging
- complicated instructions
- visible loading screens
- dependency on network connectivity

The doctor should understand the game without an MR explaining it.

If the doctor needs 30 seconds of explanation before touching the screen, UX has failed.

---

# 3. Inspiration and Technical Basis

Google's Quick Draw dataset contains millions of player drawings across hundreds of categories and is published for developers/researchers. It contains timestamped vector drawings and is licensed CC BY 4.0. The dataset also includes a published tutorial/model approach for building drawing classifiers.

Reference:

- https://github.com/googlecreativelab/quickdraw-dataset

Important:

We are **inspired by the interaction model**, not cloning Google's UI, branding, website, or proprietary service.

Do not call Google's Quick Draw API at runtime.

Do not make the application depend on quickdraw.withgoogle.com.

Do not use a remote AI endpoint.

The classifier must be bundled locally.

---

# 4. Target Environment

## Primary hardware

Moto Pad Pro 12.7" Android tablet with stylus.

## Primary browser

Google Chrome for Android.

## Deployment

Existing Laravel application hosted on cPanel shared hosting.

## Expected server capability

Standard PHP/Laravel shared hosting is sufficient.

No:

- Node server
- Python runtime
- GPU
- WebSocket server
- Redis
- queue worker
- Docker
- VPS
- AI API

is required for the core game.

---

# 5. High-Level Architecture

```text
                         INTERNET
                            │
                            │ first load / updates
                            ▼
                  ┌─────────────────────┐
                  │ Laravel + cPanel    │
                  │ Shared Hosting      │
                  └─────────┬───────────┘
                            │
                            │ HTTPS
                            ▼
                  ┌─────────────────────┐
                  │ Moto Pad Pro        │
                  │                     │
                  │ Chrome / PWA        │
                  │                     │
                  │ ┌─────────────────┐ │
                  │ │ Game UI         │ │
                  │ ├─────────────────┤ │
                  │ │ Canvas          │ │
                  │ ├─────────────────┤ │
                  │ │ Game Engine     │ │
                  │ ├─────────────────┤ │
                  │ │ TensorFlow.js   │ │
                  │ ├─────────────────┤ │
                  │ │ Local AI Model  │ │
                  │ ├─────────────────┤ │
                  │ │ Service Worker  │ │
                  │ └─────────────────┘ │
                  │                     │
                  │  OFFLINE            │
                  │  ↓                  │
                  │  Still works       │
                  └─────────────────────┘
```

The server's role is primarily:

- deliver application files
- deliver model files
- deliver PWA manifest
- deliver service worker
- optionally receive analytics later
- publish updated versions

The server must never be required during gameplay.

---

# 6. Laravel Integration

Create a dedicated route:

```php
Route::get('/doctor-toys/draw', function () {
    return view('doctor-toys.draw');
});
```

If the existing project has authentication/middleware, determine whether this route should bypass normal user authentication.

The game should preferably be accessible through a simple URL such as:

```text
https://YOUR-DOMAIN/doctor-toys/draw
```

Future launcher:

```text
https://YOUR-DOMAIN/doctor-toys
```

Recommended Blade structure:

```text
resources/
└── views/
    └── doctor-toys/
        ├── draw.blade.php
        └── components/

public/
└── doctor-toys/
    └── draw/
        ├── css/
        ├── js/
        ├── model/
        ├── assets/
        ├── icons/
        ├── audio/
        ├── manifest.webmanifest
        └── sw.js
```

Use Vite if it already exists in the project.

However, do not introduce a new build system merely for this feature.

Keep deployment compatible with the existing Laravel/cPanel setup.

---

# 7. Core User Journey

## State 1 — Landing

Screen should be extremely minimal.

Example:

```text
                 EXPONIT LABS

              ✦ DRAW SOMETHING

            I'll try to guess it.

                 [ START ]
```

Optional microcopy:

> 20 seconds. One drawing. Let's see if I'm right.

No registration.

No doctor name.

No login.

No form.

---

# 8. State 2 — Challenge

Show:

```text
             DRAW A

               🚲
            BICYCLE

             20
```

Better version:

```text
DRAW:

BICYCLE

20 seconds
```

The target object should be text, not an image, so the doctor must interpret the concept themselves.

Do not display an example drawing before the user starts.

---

# 9. Countdown

Use:

```text
3
2
1
GO
```

Duration:

- 3 seconds maximum
- allow user to start immediately if desired
- avoid long animation

Then begin timer.

---

# 10. Drawing Canvas

Canvas must occupy most of the 12.7" screen.

Recommended layout:

```text
┌───────────────────────────────────────────────┐
│ DRAW: BICYCLE                    00:17        │
│                                               │
│                                               │
│                                               │
│               DRAWING AREA                    │
│                                               │
│                                               │
│                                               │
│                                               │
│                         [CLEAR] [DONE]        │
└───────────────────────────────────────────────┘
```

Canvas requirements:

- support stylus
- support touch
- support pointer events
- no scrolling while drawing
- prevent accidental browser gestures
- high-DPI rendering
- smooth strokes
- low latency
- pressure support if available
- palm rejection as far as browser APIs permit
- no visible browser selection
- no text selection
- no accidental page zoom

Use Pointer Events.

Example conceptual event handling:

```javascript
canvas.addEventListener('pointerdown', ...);
canvas.addEventListener('pointermove', ...);
canvas.addEventListener('pointerup', ...);
canvas.addEventListener('pointercancel', ...);
```

Use:

```css
touch-action: none;
user-select: none;
```

where appropriate.

---

# 11. Drawing Data Representation

Maintain two representations.

## A. Visual representation

Canvas bitmap used for rendering.

## B. Stroke representation

Store the actual stroke sequence:

```javascript
[
  {
    points: [
      {x: 0.21, y: 0.42, t: 0},
      {x: 0.22, y: 0.43, t: 16},
      ...
    ]
  },
  ...
]
```

Normalize coordinates to:

```text
x: 0 → 1
y: 0 → 1
```

This prevents the model from depending on screen resolution.

Record:

- x
- y
- timestamp
- stroke start/end
- optional pressure
- optional pointer type

Do not persist raw drawings to the server by default.

---

# 12. AI Classification Strategy

## Recommended initial approach

Use a local TensorFlow.js model.

Two viable approaches:

### Option A — Bitmap CNN

Convert the drawing into a standardized grayscale bitmap.

Example:

```text
28x28
64x64
96x96
```

Run through a compact CNN.

Advantages:

- simpler
- easier to debug
- easier to train
- easy TensorFlow.js deployment

### Option B — Stroke-sequence model

Use x/y/time/stroke information.

Advantages:

- closer to the original Quick Draw representation
- retains drawing order
- potentially better use of stylus information

For MVP, choose Option A unless benchmark testing proves Option B materially better.

Do not over-engineer the first release.

---

# 13. Model Categories

Do NOT start with all 345 Quick Draw categories.

Use approximately 20–30 categories.

Initial recommended categories:

```text
apple
banana
bicycle
car
cat
circle
coffee cup
dog
flower
guitar
heart
house
key
laptop
moon
pizza
rocket
shoe
smiley face
star
sun
tree
umbrella
watch
phone
```

Final categories should be chosen based on:

- recognizability
- visual distinctiveness
- likelihood a doctor can draw them quickly
- model accuracy
- entertainment value

Avoid ambiguous categories during MVP.

Example bad pair:

```text
phone / tablet
```

Example good pair:

```text
banana / house
```

---

# 14. Model Training

Use publicly available Quick Draw data where appropriate.

The official dataset is CC BY 4.0.

Retain attribution/documentation in the project.

Do not blindly train on all available data.

For each selected class:

1. Download a suitable subset.
2. Preprocess drawings.
3. Normalize coordinates.
4. Render or otherwise transform into the chosen input representation.
5. Split:
   - training
   - validation
   - test
6. Train a compact model.
7. Evaluate per-category accuracy.
8. Remove categories that perform poorly.
9. Convert/export to TensorFlow.js format.
10. Benchmark on the actual Moto Pad.

Do not judge performance solely on desktop Chrome.

The Moto Pad is the actual target.

---

# 15. Model Requirements

Target:

- small download size
- fast startup
- fast inference
- acceptable accuracy
- no visible lag

Initial target:

```text
Model load:
< 2 seconds after cached

Inference:
< 100 ms target

UI response:
60 FPS target

First prediction:
< 1 second after sufficient drawing
```

These are engineering targets, not guarantees.

If the model is too large, reduce:

- number of classes
- input resolution
- layer count
- channel count
- model precision/format

Do not sacrifice the UX to chase maximum classification accuracy.

The doctor only needs the app to feel smart.

---

# 16. Continuous Guessing

This is critical.

Do not wait until the doctor presses DONE to show a prediction.

While the doctor draws:

```text
You draw...
```

Then:

```text
I think it's...

CAR        41%
```

Then:

```text
CAR        68%
```

Then:

```text
CAR        83%
```

This creates the "AI is watching me draw" effect.

Use a throttled inference loop.

Example:

```text
Run inference every 250–500 ms
```

Do not run inference on every pointermove event.

---

# 17. Prediction UI

Do not show a boring probability table.

Use one dominant prediction.

Example:

```text
I THINK IT'S...

🚗

CAR
```

Optionally show a tiny confidence meter.

Avoid exposing raw technical probability unless useful.

Bad:

```text
car 0.823761
truck 0.08123
bus 0.03122
```

Good:

```text
I THINK IT'S A CAR
```

---

# 18. Final Result

When time ends or user taps DONE:

```text
               I GOT IT!

                  🚗

                 CAR

             Nice drawing.
```

Or intentionally humorous:

```text
I WAS 87% SURE.

                 🚗

              CAR
```

If wrong:

```text
YOU GOT ME.

You drew:
[unknown]

I guessed:
[wrong guess]

Again?
```

The wrong state is important.

AI being wrong can actually make the experience more fun.

---

# 19. Replay Loop

After result:

```text
[ DRAW AGAIN ]

[ NEW CHALLENGE ]
```

Do not send the doctor to a menu.

The next interaction should be one tap away.

Recommended:

- same challenge again
- new random challenge
- exit

---

# 20. Challenge Selection

Use a local challenge list:

```javascript
const challenges = [
  'car',
  'bicycle',
  'house',
  'dog',
  ...
];
```

Avoid repeating the same object immediately.

Algorithm:

```text
current != previous
```

Optional difficulty:

```text
Easy
Medium
Hard
```

But do not expose difficulty in MVP unless it improves the experience.

---

# 21. UX Design Direction

This should NOT look like:

- a school app
- an educational quiz
- a pharma presentation
- a children's cartoon
- a generic Bootstrap website

Visual direction:

- premium
- minimal
- large typography
- generous whitespace
- subtle animation
- fast transitions
- tactile buttons
- sophisticated sound design
- Exponit branding used sparingly

Think:

```text
Apple-like product interaction
+
Neal.fun curiosity
+
Quick Draw simplicity
```

---

# 22. Tablet Orientation

Primary:

```text
Landscape
```

Target:

```text
2560 × 1600 class display
```

The UI must be responsive.

Do not hardcode a single pixel resolution.

Use viewport-relative layout.

Support:

- 16:10
- 16:9
- common Android tablet landscape sizes

---

# 23. Stylus Behavior

Use Pointer Events.

Detect:

```javascript
event.pointerType
```

Possible values:

```text
pen
touch
mouse
```

Preferred behavior:

- pen = drawing
- touch = drawing only if no accidental palm issue
- mouse = drawing for development/testing

If pen input is available, optimize for it.

Do not require stylus hardware for functionality.

---

# 24. Clear Button

Clear should be easy to access but not visually dominant.

Use:

```text
CLEAR
```

Do not show browser confirm dialogs.

Clear should:

1. clear canvas
2. reset stroke array
3. reset model input
4. reset predictions
5. continue timer

---

# 25. Done Button

DONE should:

1. stop timer
2. run final inference
3. display final result
4. record analytics locally
5. move to result state

If there is no drawing:

```text
Draw something first.
```

Do not treat an empty canvas as a valid prediction.

---

# 26. Offline Architecture

Use a Service Worker.

Service workers can intercept requests and serve cached resources when the network is unavailable. They are a standard mechanism for offline PWA behavior.

Reference:

https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API

Use a versioned cache:

```javascript
const CACHE_NAME = 'exponit-draw-v1';
```

Cache all required resources:

```text
/
/doctor-toys/draw
/doctor-toys/draw/css/...
/doctor-toys/draw/js/...
/doctor-toys/draw/model/...
/doctor-toys/draw/assets/...
/doctor-toys/draw/audio/...
/doctor-toys/draw/manifest.webmanifest
```

Do NOT rely on CDN resources for runtime functionality.

---

# 27. Service Worker Strategy

Use:

### App shell

Cache-first.

### Model

Cache-first and versioned.

### Static assets

Cache-first.

### API analytics

Network-only with local queue fallback.

Example:

```text
GET static asset
        ↓
Cache available?
  ├── YES → return cache
  └── NO → network → cache response
```

For analytics:

```text
POST analytics
        ↓
Online?
 ├── YES → send
 └── NO → save locally
```

Do not cache POST requests as normal page assets.

---

# 28. Cache Versioning

Use explicit versioning:

```text
draw-v1
draw-v2
draw-v3
```

When deploying a new model:

```text
MODEL_VERSION = 2
CACHE_VERSION = draw-v2
```

On service-worker activation:

1. create new cache
2. delete old caches
3. claim clients
4. reload/update gracefully

Never leave multiple obsolete model versions consuming storage unnecessarily.

---

# 29. PWA Manifest

Create:

```text
manifest.webmanifest
```

Example structure:

```json
{
  "name": "Exponit Draw",
  "short_name": "Exponit Draw",
  "start_url": "/doctor-toys/draw",
  "display": "standalone",
  "orientation": "landscape",
  "background_color": "#ffffff",
  "theme_color": "#001B54",
  "icons": []
}
```

Add suitable icons.

Do not make the app open into the normal Laravel website when launched as a PWA.

---

# 30. HTTPS Requirement

The application must be served through HTTPS.

Service workers require a secure context in normal production deployment.

Therefore verify:

```text
https://domain.com/doctor-toys/draw
```

works correctly.

Do not test offline functionality using:

```text
http://...
```

on production.

---

# 31. First-Run Offline Preparation

Important:

The app cannot magically become available offline before its first resources have been downloaded.

Therefore first visit must complete:

```text
Download app shell
+
Download model
+
Download assets
+
Register service worker
+
Verify cache
```

Display a one-time setup state if required:

```text
Preparing Exponit Draw...

██████████████░░ 82%

Ready for offline use.
```

Then:

```text
✓ Offline mode ready
```

Only after this should the MR rely on airplane-mode operation.

---

# 32. Offline Readiness Check

Create an internal function:

```javascript
isOfflineReady()
```

It should verify that:

- service worker is registered
- required cache exists
- model files are available
- app shell is available
- TensorFlow.js is locally available

Show:

```text
OFFLINE READY ✓
```

in a hidden/debug panel, not necessarily in the normal UI.

---

# 33. Local Model Loading

Do not use:

```javascript
https://cdn...
```

for the model.

Use a local path such as:

```text
/doctor-toys/draw/model/model.json
```

and local shard files.

Example conceptual loading:

```javascript
const model = await tf.loadLayersModel(
    '/doctor-toys/draw/model/model.json'
);
```

The exact model-loading API must match the exported model format.

---

# 34. TensorFlow.js Packaging

Prefer bundling the required TensorFlow.js runtime locally.

Do not make runtime inference dependent on:

- TensorFlow CDN
- external API
- Google API
- OpenAI API
- third-party inference endpoint

If using a bundler, include TensorFlow.js in the built asset.

If using static files, place the exact production-compatible TensorFlow.js build in:

```text
public/doctor-toys/draw/js/
```

---

# 35. Model Loading UX

The model should be loaded before the game begins.

Do not wait until the first drawing.

Sequence:

```text
Open app
   ↓
Initialize TensorFlow.js
   ↓
Load model
   ↓
Warm up model with dummy input
   ↓
Ready
```

Warmup is useful because the first inference may be slower due to backend initialization.

---

# 36. TensorFlow Backend

Test available browser backends on the actual Moto Pad.

Potential:

```text
WebGL
WASM
CPU
```

Prefer the fastest stable backend supported by the device/browser.

Do not assume WebGL is always best.

Benchmark:

```text
backend
model load time
first inference
average inference
memory behavior
```

Select the most reliable option for the target device.

---

# 37. Error Handling

The app must never show a technical error to the doctor.

Bad:

```text
TensorFlow error:
Cannot read properties of undefined...
```

Good:

```text
Hmm... you got me.

Try again.
```

Developer diagnostics should be available behind:

```text
?debug=1
```

or a hidden debug gesture.

---

# 38. Debug Panel

Create a developer-only panel.

Show:

```text
Exponit Draw Debug

App version: 1.0.0
Model version: 1
SW version: draw-v1

Online: YES/NO
Service Worker: ACTIVE/NO
Offline Ready: YES/NO

TF.js: loaded
Backend: webgl

Model: loaded
Model size: XX MB

Inference:
Average: XX ms
Last: XX ms

Canvas:
2560 × 1600

Pointer:
pen/touch/mouse

Cached assets:
XX
```

This will save significant debugging time in the field.

---

# 39. Analytics

Analytics must NOT be required for gameplay.

Create local event records.

Example:

```javascript
{
  event: "game_completed",
  session_id: "...",
  challenge: "car",
  guessed: "car",
  correct: true,
  duration_ms: 18400,
  inference_count: 42,
  model_version: "1.0",
  app_version: "1.0",
  timestamp: "..."
}
```

Avoid storing:

- doctor name
- patient name
- phone number
- medical data
- prescription information
- identifiable patient data

unless a future explicit business requirement requires it and the privacy implications are separately designed.

For MVP, keep analytics anonymous.

---

# 40. Local Analytics Queue

Use IndexedDB for durable local event storage.

Conceptual:

```text
IndexedDB
└── events
    ├── event-001
    ├── event-002
    └── event-003
```

When online:

```text
queue → POST Laravel endpoint
```

When offline:

```text
queue remains locally
```

On successful server response:

```text
delete successfully synced events
```

Never delete before successful acknowledgement.

---

# 41. Analytics API

Optional endpoint:

```text
POST /api/doctor-toys/events
```

Expected body:

```json
{
  "events": []
}
```

The endpoint should accept batches.

Do not make one HTTP request per interaction.

Batch events.

Potential batch trigger:

```text
5–20 events
```

or:

```text
app regains connectivity
```

or:

```text
app closes/visibility changes
```

Do not depend on background sync being available.

---

# 42. Security

Do not trust client analytics.

Treat analytics as informational only.

Do not expose:

- API secrets
- database credentials
- private admin endpoints
- model-training credentials

in frontend JavaScript.

The model itself is public because it is intentionally downloaded to the device.

That is acceptable.

---

# 43. Data Privacy

The MVP should operate without collecting personal data.

Do not identify the doctor.

Use an anonymous locally generated session identifier if needed.

Example:

```text
crypto.randomUUID()
```

Do not persist sensitive personal information.

Do not record the actual drawing image unless explicitly required.

The safest default is:

```text
drawing → inference → discard
```

Only retain aggregate/anonymous interaction statistics.

---

# 44. Performance Requirements

The experience should feel instant.

Targets:

```text
Initial cached launch:
< 1.5 sec

Model already cached:
< 2 sec initialization

Canvas input latency:
minimal / visually immediate

Inference:
< 100 ms target

Animation:
~60 FPS

Result transition:
< 300 ms
```

If the target device cannot meet these targets, optimize before adding features.

---

# 45. Memory Management

Drawing can generate thousands of points.

Do not store unlimited pointer events.

Apply point simplification if necessary.

Possible strategies:

- sample pointer events
- simplify lines
- cap points per stroke
- normalize after stroke completion

Keep the current drawing lightweight.

Do not repeatedly allocate large tensors.

Dispose TensorFlow tensors:

```javascript
tensor.dispose();
```

Use:

```javascript
tf.tidy(...)
```

where appropriate.

Memory leaks will become visible during repeated games.

---

# 46. Model Inference Pipeline

Recommended:

```text
Pointer input
      ↓
Stroke buffer
      ↓
Canvas rendering
      ↓
Debounce / throttle
      ↓
Rasterize
      ↓
Normalize
      ↓
Tensor
      ↓
Model.predict()
      ↓
Softmax probabilities
      ↓
Top prediction
      ↓
UI update
```

Never execute expensive preprocessing on every pointer event.

---

# 47. Input Normalization

The model must receive a standardized image regardless of where the doctor drew.

Process:

1. capture drawing bounds
2. crop to bounding box
3. add padding
4. scale to model input size
5. center image
6. convert to grayscale if model expects grayscale
7. normalize pixel values
8. create tensor

This significantly improves recognition compared with feeding the raw full-screen canvas.

---

# 48. Stroke Rendering

Use a polished stroke style.

Recommended:

- rounded caps
- rounded joins
- medium stroke width
- pressure-sensitive width if reliable
- no jagged rendering
- subtle animation

Do not use a thin 1px technical line.

The drawing should feel like a premium digital pen.

---

# 49. Sound Design

Optional but recommended.

Use tiny local audio assets:

```text
countdown
tick
prediction
success
failure
```

All sounds must be bundled locally.

Do not require external audio URLs.

Respect browser autoplay restrictions.

Sound should begin only after user interaction.

Provide a mute setting.

Default volume should be subtle.

---

# 50. Branding

Do not plaster Exponit everywhere.

Recommended:

Landing:

```text
EXPONIT LABS
```

During game:

small logo or wordmark.

Result:

small brand mark.

The experience should be remembered as:

> "That cool thing Exponit showed me."

not:

> "That pharma advertisement."

---

# 51. Pharma Compliance Boundary

This first feature is a general entertainment/engagement toy.

Do not make medical claims.

Do not imply diagnostic capability.

Do not suggest the AI can diagnose a disease.

Do not display therapeutic claims inside this game.

If product integration is added later, create a separate reviewed experience.

The drawing classifier is an entertainment classifier, not a medical AI.

---

# 52. Accessibility

Support:

- high contrast
- large text
- touch input
- stylus input
- reduced motion preference where practical
- mute
- clear visual feedback

Do not make color the only indicator.

---

# 53. Browser Controls

When the game is active:

- prevent page scrolling
- prevent text selection
- prevent long-press selection where appropriate
- avoid accidental navigation
- preserve landscape layout
- avoid accidental pinch zoom

Do not attempt to lock down the entire Android OS from the web app.

---

# 54. Fullscreen

Optional.

If supported reliably, provide:

```text
Enter full screen
```

Do not make fullscreen a dependency.

PWA standalone mode is preferable for field use.

---

# 55. Installation Flow

MR setup:

1. Open URL once.
2. Wait for "Offline Ready".
3. Add to home screen.
4. Open Exponit Draw from home screen.
5. Test airplane mode.
6. Confirm game still works.

Create a hidden/setup screen:

```text
Device Setup

✓ App loaded
✓ Model downloaded
✓ Service worker active
✓ Offline cache ready

[ TEST OFFLINE MODE ]
```

---

# 56. Offline Test Procedure

This is mandatory.

### Test 1

Open online.

### Test 2

Wait until:

```text
Offline Ready
```

### Test 3

Close Chrome/PWA.

### Test 4

Enable airplane mode.

### Test 5

Open Exponit Draw.

### Test 6

Start game.

### Test 7

Draw using stylus.

### Test 8

Confirm AI predictions appear.

### Test 9

Complete game.

### Test 10

Replay.

### Test 11

Open another challenge.

Everything above must work.

---

# 57. Network Failure Tests

Test:

- offline at launch
- network disappears during game
- network disappears while loading
- network disappears after result
- network returns during game
- network returns after multiple queued events
- stale service worker
- failed model download
- partially downloaded model
- browser cache cleared
- low storage
- page reload

---

# 58. Deployment on cPanel

Assume normal Laravel cPanel hosting.

Deployment:

```text
Laravel project
      ↓
resources/views/doctor-toys
      ↓
public/doctor-toys/draw
      ↓
upload/deploy normally
```

Ensure correct MIME types for:

```text
.json
.bin
.webmanifest
.js
```

The server must return model files without corrupting them.

If the host applies compression or security rules, verify the model loads correctly.

---

# 59. Laravel Route vs Static Route

Preferred:

```php
Route::view(
    '/doctor-toys/draw',
    'doctor-toys.draw'
);
```

Blade page loads local assets.

Example:

```blade
<link
    rel="manifest"
    href="{{ asset('doctor-toys/draw/manifest.webmanifest') }}"
>
```

Use Laravel's `asset()` helper so deployment paths remain configurable.

---

# 60. Avoid External Dependencies

Runtime must not require:

```text
Google Fonts
CDNJS
unpkg
jsDelivr
Google APIs
OpenAI APIs
Firebase
Supabase
remote image APIs
remote analytics
```

If a library is needed:

- bundle it locally
- cache it
- version it

---

# 61. Recommended Project Structure

```text
resources/
└── views/
    └── doctor-toys/
        └── draw.blade.php

public/
└── doctor-toys/
    └── draw/
        ├── assets/
        │   ├── logo.svg
        │   ├── icons/
        │   └── audio/
        │
        ├── css/
        │   └── draw.css
        │
        ├── js/
        │   ├── app.js
        │   ├── game.js
        │   ├── canvas.js
        │   ├── classifier.js
        │   ├── storage.js
        │   ├── analytics.js
        │   └── debug.js
        │
        ├── model/
        │   ├── model.json
        │   └── *.bin
        │
        ├── icons/
        │   ├── icon-192.png
        │   └── icon-512.png
        │
        ├── manifest.webmanifest
        └── sw.js
```

---

# 62. JavaScript Module Responsibilities

## app.js

Application bootstrap.

Responsibilities:

- initialize app
- register service worker
- load model
- initialize game
- initialize analytics
- initialize debug mode

## game.js

Game state machine.

States:

```text
BOOT
READY
COUNTDOWN
DRAWING
RESULT
ERROR
```

## canvas.js

Responsibilities:

- pointer events
- stroke rendering
- clear
- normalization
- bounding box
- bitmap generation

## classifier.js

Responsibilities:

- TensorFlow.js initialization
- backend selection
- model loading
- preprocessing
- inference
- top prediction

## storage.js

Responsibilities:

- IndexedDB
- analytics queue
- app metadata
- offline readiness

## analytics.js

Responsibilities:

- create events
- queue locally
- sync when possible

## debug.js

Developer diagnostics.

---

# 63. Game State Machine

Implement explicitly.

```text
BOOT
  ↓
INITIALIZING
  ↓
READY
  ↓
COUNTDOWN
  ↓
DRAWING
  ├── CLEAR → DRAWING
  └── DONE → RESULT
  ↓
RESULT
  ├── REPLAY → COUNTDOWN
  └── NEW CHALLENGE → COUNTDOWN
```

Do not scatter game state across random booleans.

Use one central state.

---

# 64. Prediction Smoothing

Raw predictions may jump:

```text
car
truck
car
bus
car
```

Do not expose this directly.

Use smoothing.

For example:

```text
keep last N predictions
average probabilities
```

or exponential smoothing.

Only update visible prediction when confidence crosses a threshold.

Example:

```text
confidence > 0.35
```

Threshold must be tuned experimentally.

---

# 65. Prediction Copy

Possible copy:

```text
I'M THINKING...

I SEE IT...

IS IT A CAR?

I THINK IT'S...

I KNOW THIS ONE.

YOU GOT ME.

NICE TRY.
```

Randomize copy slightly.

Do not make every round feel identical.

---

# 66. Game Timing

Default:

```text
20 seconds
```

Possible later modes:

```text
10 sec — Fast
20 sec — Normal
30 sec — Relaxed
```

MVP should use one duration.

---

# 67. Challenge Difficulty

Don't start with an algorithmically complex difficulty system.

Initial static difficulty:

```text
Easy:
circle
star
house
sun
heart

Medium:
car
bicycle
dog
coffee cup
guitar

Hard:
camera
laptop
umbrella
rocket
watch
```

But only use categories that benchmark well.

---

# 68. Result Celebration

Keep it under 2 seconds.

Example:

```text
✓
I GOT IT

CAR
```

Small particle animation is acceptable.

Do not use a huge childish confetti explosion.

The target audience is an adult professional.

---

# 69. Replay Psychology

At result screen, prioritize:

```text
DRAW AGAIN
```

above:

```text
EXIT
```

Potential copy:

```text
Think you can fool me?

[ DRAW AGAIN ]
```

This is more engaging than:

```text
Game complete.
```

---

# 70. Optional "Impossible Mode"

Future feature.

After successful rounds:

```text
Want to try something harder?

[ YES ]
```

Use more ambiguous categories.

Do not include in MVP unless basic loop is excellent.

---

# 71. Optional Doctor Challenge Mode

Future:

```text
YOU vs AI

3 rounds

Score:
Doctor: 2
AI: 1
```

Do not build until single-player mode is proven.

---

# 72. Optional Share Mode

Future:

Generate an image:

```text
Doctor's drawing
+
AI guess
+
Exponit Draw
```

Could be shared over WhatsApp if the doctor wants.

This is optional and must not interfere with the core experience.

---

# 73. Optional Product Integration

Future architecture should support:

```text
Toy
 ↓
Result
 ↓
Related educational experience
```

Example:

```text
Draw a stomach
 ↓
Result
 ↓
"Want to see what happens inside?"
 ↓
separate clinical visualization
```

Do not inject product promotion into the first version.

The first version's job is to establish the interaction.

---

# 74. Analytics KPIs

Track:

```text
launches
games_started
games_completed
games_abandoned
correct_predictions
incorrect_predictions
average_duration
replays
challenge_frequency
offline_sessions
online_sessions
model_load_time
inference_time
```

Derived:

```text
completion_rate
replay_rate
prediction_accuracy
average_session_length
```

Most important:

### Replay rate

If doctors voluntarily play again, the concept works.

---

# 75. Recommended Event Schema

```json
{
  "event": "game_completed",
  "session_id": "uuid",
  "challenge": "car",
  "prediction": "car",
  "correct": true,
  "duration_ms": 18200,
  "model_version": "1.0.0",
  "app_version": "1.0.0",
  "offline": true,
  "timestamp": "2026-09-28T00:00:00Z"
}
```

Never include raw patient information.

---

# 76. Development Milestones

## Milestone 1 — UI Prototype

No AI.

Build:

- landing
- challenge
- canvas
- timer
- clear
- done
- result
- replay

Acceptance:

Doctor can understand and play without developer assistance.

---

## Milestone 2 — Local AI

Add:

- TensorFlow.js
- local model
- preprocessing
- prediction
- prediction UI

Acceptance:

Works without network after model is cached.

---

## Milestone 3 — PWA

Add:

- manifest
- service worker
- caching
- offline readiness
- installability

Acceptance:

Full game works in airplane mode.

---

## Milestone 4 — Optimization

Measure:

- load time
- inference latency
- memory
- FPS

Acceptance:

Smooth on Moto Pad.

---

## Milestone 5 — Analytics

Add:

- IndexedDB
- local queue
- optional sync API

Acceptance:

Gameplay remains unaffected if analytics endpoint is unavailable.

---

## Milestone 6 — Field Test

Give tablet to an MR.

Do not explain the game.

Observe:

- Does doctor understand?
- How quickly do they touch the tablet?
- Do they use stylus?
- Do they finish?
- Do they replay?
- Do they smile/laugh?
- Do they ask "what is this?"
- Do they remember it later?

These observations are more valuable than developer assumptions.

---

# 77. Definition of Done

The feature is DONE only when all are true:

### Functional

- [ ] Landing works
- [ ] Challenge appears
- [ ] Countdown works
- [ ] Stylus drawing works
- [ ] Touch drawing works
- [ ] Clear works
- [ ] Done works
- [ ] Local AI predicts
- [ ] Prediction updates during drawing
- [ ] Result works
- [ ] Replay works
- [ ] New challenge works

### Offline

- [ ] Service worker installed
- [ ] App shell cached
- [ ] TensorFlow.js cached
- [ ] Model cached
- [ ] Assets cached
- [ ] App works in airplane mode
- [ ] AI works in airplane mode
- [ ] No CDN dependency
- [ ] No API dependency

### Performance

- [ ] No visible drawing lag
- [ ] No major memory leak
- [ ] Inference acceptable on Moto Pad
- [ ] Model loads reliably
- [ ] Multiple consecutive rounds work

### UX

- [ ] Doctor can understand without explanation
- [ ] No unnecessary form
- [ ] No login
- [ ] No technical UI
- [ ] Large touch targets
- [ ] Premium appearance
- [ ] Fast transitions
- [ ] Replay is obvious

### Deployment

- [ ] Works on cPanel
- [ ] HTTPS enabled
- [ ] Laravel route works
- [ ] Model MIME type works
- [ ] PWA manifest works
- [ ] Service worker scope correct

---

# 78. What NOT To Do

Do NOT:

1. Build a React app unless the existing project already uses it and there is a compelling reason.
2. Add a Node backend.
3. Add Python inference server.
4. Use an AI API.
5. Depend on Google Quick Draw's live website.
6. Depend on CDN TensorFlow.js.
7. Make the game require login.
8. Add a database dependency to gameplay.
9. Store every drawing on the server.
10. Build 10 games before validating one.
11. Add medical content to this MVP.
12. Make the UI look like a pharma visual aid.
13. Show a spinner during every inference.
14. Run inference on every pointer event.
15. Ignore memory leaks.
16. Assume desktop performance represents the Moto Pad.
17. Claim "offline" until it has been tested in airplane mode.

---

# 79. Recommended Technology Stack

## Frontend

```text
HTML5
CSS3
Vanilla JavaScript
Canvas API
Pointer Events
TensorFlow.js
IndexedDB
Service Worker
Web App Manifest
```

Use vanilla JS unless there is an existing frontend framework that makes integration substantially easier.

The feature is small enough that adding a framework can create unnecessary complexity.

## Backend

```text
Laravel
PHP
existing cPanel hosting
```

Backend is optional for gameplay.

## Storage

```text
Cache API
IndexedDB
```

## AI

```text
TensorFlow.js
custom compact classifier
Quick Draw dataset / other appropriately licensed training data
```

---

# 80. External References for the Developer

### Quick Draw Dataset

https://github.com/googlecreativelab/quickdraw-dataset

The dataset documentation states that it contains 50 million drawings across 345 categories and is released under CC BY 4.0.

### Quick Draw Dataset Categories

https://github.com/googlecreativelab/quickdraw-dataset/blob/master/categories.txt

### TensorFlow.js / Quick Draw model training reference

The Quick Draw dataset repository links to TensorFlow's tutorial/model approach for training a drawing classifier.

### Service Workers

https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API

### PWA Caching

https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Caching

### PWA Offline Operation

https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation

---

# 81. AI Agent Implementation Instructions

You are the coding agent implementing this specification.

Before modifying code:

1. Inspect the existing Laravel project.
2. Determine Laravel version.
3. Determine current frontend/build setup.
4. Determine whether Vite is already configured.
5. Determine public asset conventions.
6. Determine existing authentication/middleware.
7. Do not replace the existing architecture.
8. Reuse existing conventions wherever practical.

Then implement incrementally.

### First deliverable

Build Milestone 1 completely:

- polished UI
- canvas
- pointer/stylus input
- timer
- challenge system
- result state
- replay

Do NOT begin model training/integration until the interaction loop works.

### Second deliverable

Integrate a small local classifier.

### Third deliverable

Implement offline PWA.

### Fourth deliverable

Implement analytics.

### Fifth deliverable

Optimize on the actual Moto Pad.

---

# 82. Critical Agent Rule

Do not fake offline functionality.

A page being loaded from browser cache is not enough.

The agent must verify:

```text
1. First online load
2. Service worker installation
3. Cache population
4. Close/reopen
5. Airplane mode
6. Full game
7. AI inference
8. Replay
```

Only then mark offline support complete.

---

# 83. Critical Agent Rule — Do Not Overbuild

The purpose of this feature is to create a **memorable doctor interaction**, not to demonstrate engineering complexity.

If a simpler implementation achieves:

- smooth drawing
- convincing prediction
- instant feedback
- offline operation

choose the simpler implementation.

Do not introduce:

- unnecessary frameworks
- unnecessary backend services
- unnecessary databases
- unnecessary APIs
- unnecessary infrastructure

---

# 84. Final Product Standard

The final experience should feel like:

> "Wait... what is this?"

Then:

> "Give me the pen."

Then:

> "Haha, it actually guessed it."

Then:

> "Try again."

That is the success condition.

Not:

> "Nice pharma app."

---

# 85. Suggested First Challenge Set

Use only high-confidence categories initially:

```text
circle
star
heart
house
sun
car
bicycle
dog
cat
flower
tree
pizza
coffee cup
guitar
rocket
watch
phone
camera
umbrella
airplane
```

Run real-device tests.

Remove any category that repeatedly produces frustrating predictions.

The game should prefer **entertainment value over category count**.

---

# 86. Future Expansion Architecture

After this feature is stable:

```text
/doctor-toys
│
├── /draw
│     └── Quick Draw experience
│
├── /guess
│     └── visual guessing
│
├── /memory
│     └── memory challenge
│
├── /create
│     └── generative art
│
├── /reaction
│     └── reaction challenge
│
└── /lab
      └── future interactive educational experiences
```

Each experience should share:

```text
PWA shell
analytics
offline storage
branding
navigation
debug panel
versioning
```

but keep the actual games modular.

---

# 87. MVP Success Threshold

Before expanding beyond Quick Draw, aim for:

```text
≥ 80% of doctors understand without explanation
≥ 70% complete the first round
≥ 30% voluntarily replay
```

These are internal product-development targets, not industry benchmarks.

If replay is poor, do not build more features.

Fix the interaction.

If doctors need explanation, fix the first screen.

If prediction is poor, reduce categories.

If drawing feels laggy, optimize input before adding visual effects.

---

# 88. Final Instruction to Coding Agent

Build this as a **production-quality offline PWA feature inside the existing Laravel application**.

The app must:

- run on cPanel shared hosting
- run in Chrome Android
- be optimized for a 12.7" landscape tablet
- support stylus input
- run AI inference locally
- use a locally bundled model
- work without internet after first successful preparation
- have no runtime CDN dependency
- have no runtime AI API dependency
- use a Service Worker
- use IndexedDB where persistent local state is needed
- maintain clean separation between UI, game logic, canvas, classifier, storage, and analytics
- be easy to extend into future Doctor Toys

Do not stop at a static mockup.

Implement the actual interaction.

Do not claim offline support until airplane-mode testing succeeds.

Do not add unnecessary infrastructure.

The first goal is a **small, fast, polished, addictive 20-second drawing experience** that a doctor can understand immediately.

