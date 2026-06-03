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
        data-marker-aspect="{{ $creative->markerAspectRatio() }}"></div>

    {{-- Tap-to-start gate: required so phones allow the camera and video sound. --}}
    <div id="ar-start" class="overlay">
        <h1>{{ $creative->name }}</h1>
        <p>Point your camera at the printed image to bring it to life. Tap below to begin.</p>
        <button id="ar-start-button" class="btn" type="button">Start experience</button>
    </div>

    {{-- Scanning hint, shown once the camera is live and hidden while the video plays. --}}
    <div id="ar-status" class="hidden">Point at the image…</div>

    {{-- Fallback for browsers that can't open the camera (e.g. in-app QR browsers). --}}
    <div id="ar-error" class="overlay hidden">
        <h1>Can’t start AR</h1>
        <p id="ar-error-text"></p>
    </div>
</body>

</html>
