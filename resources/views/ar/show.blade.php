<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $creative->name }} | Exponit Labs AR</title>
    <meta name="robots" content="noindex">
    @vite(['resources/css/ar.css', 'resources/js/ar.js'])
</head>

<body>
    {{-- MindAR injects the camera feed + canvas in here; data drives the JS viewer. --}}
    <div id="ar-root"
        data-mind="{{ $creative->mindFileUrl() }}"
        data-video="{{ $creative->videoUrl() }}"
        data-play-mode="{{ $creative->play_mode }}"
        data-marker-aspect="{{ $creative->markerAspectRatio() }}"
        data-recognize-url="{{ route('ar.recognize-doctor', $creative) }}"
        data-csrf="{{ csrf_token() }}"></div>

    {{-- Floating Doctor Greeting badge --}}
    <div id="ar-doctor-greeting" class="ar-badge hidden">
        <span class="ar-badge-icon">👋</span>
        <div class="ar-badge-text">
            <div id="ar-greeting-title" class="ar-badge-title">Welcome Doctor</div>
            <div id="ar-greeting-sub" class="ar-badge-sub">Exponit Labs Product Detailing</div>
        </div>
    </div>

    {{-- Floating Product narration toast --}}
    <div id="ar-product-toast" class="ar-product-toast hidden">
        <div id="ar-product-name" class="ar-product-title">GASTRONIT-DSR</div>
        <div id="ar-product-comp" class="ar-product-comp">Pantoprazole 40 mg + Domperidone 30 mg</div>
    </div>

    {{-- Interactive 3D Hint --}}
    <div id="ar-3d-hint" class="ar-3d-hint hidden">👆 Drag 3D pack to rotate</div>

    {{-- Tap-to-start gate: required so phones allow the camera and video sound. --}}
    <div id="ar-start" class="overlay">
        <h1>{{ $creative->name }}</h1>
        <p>Point your camera at the printed reminder card to begin the interactive audio & 3D product experience.</p>
        <button id="ar-start-button" class="btn" type="button">Start experience</button>
    </div>

    {{-- Scanning hint, shown once the camera is live and hidden while the video plays. --}}
    <div id="ar-status" class="hidden">Point camera at the reminder card…</div>

    {{-- Fallback for browsers that can't open the camera (e.g. in-app QR browsers). --}}
    <div id="ar-error" class="overlay hidden">
        <h1>Can’t start AR</h1>
        <p id="ar-error-text"></p>
    </div>
</body>

</html>
