<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Exponit Draw">
    <meta name="theme-color" content="#090d16">
    <title>Exponit Draw | Offline Doctor Toys</title>
    <link rel="manifest" href="{{ asset('lab/draw/manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('lab/draw/assets/icons/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('lab/draw/css/draw.css') }}">
</head>

<body>
    <div class="app-viewport">
        <!-- 1. LANDING SCREEN -->
        <section id="screen-landing" class="screen">
            <header class="screen-header">
                <span class="brand-title" id="header-landing-brand">EXPONIT LABS</span>
                <button type="button" class="icon-btn btn-mute-toggle" aria-label="Toggle Sound">🔊</button>
            </header>
            <div class="landing-card">
                <p class="landing-kicker">✦ DOCTOR TOYS</p>
                <h1 class="landing-title">DRAW SOMETHING</h1>
                <p class="landing-subtitle">I'll try to guess it in real time.</p>
                <p class="landing-microcopy">20 seconds. One drawing. Let's see if I'm right.</p>
                <button type="button" id="btn-start-game" class="btn-primary">START</button>
            </div>
        </section>

        <!-- 2. COUNTDOWN SCREEN -->
        <section id="screen-countdown" class="screen hidden">
            <div class="countdown-content">
                <p class="countdown-label">DRAW THIS</p>
                <h2 id="countdown-target-prompt" class="countdown-target">BICYCLE</h2>
                <span class="countdown-badge">20 SECONDS</span>
                <div id="countdown-number" class="countdown-number">3</div>
            </div>
        </section>

        <!-- 3. DRAWING SCREEN -->
        <section id="screen-drawing" class="screen hidden">
            <header class="screen-header">
                <span class="brand-title" id="header-app-brand">EXPONIT DRAW</span>
                <div class="drawing-header-content">
                    <span class="target-badge">DRAW: <b id="drawing-target-prompt">...</b></span>
                    <span id="timer-display" class="timer-pill">00:20</span>
                </div>
                <button type="button" class="icon-btn btn-mute-toggle" aria-label="Toggle Sound">🔊</button>
            </header>

            <!-- Live AI Guessing Ticker -->
            <div class="live-prediction-bar">
                <div id="pred-meter-bar" class="pred-meter"></div>
                <span id="pred-copy" class="pred-copy">I'M WATCHING...</span>
                <span id="pred-emoji" class="pred-emoji">✏️</span>
                <span id="pred-label" class="pred-label"></span>
                <span id="pred-confidence" class="pred-confidence"></span>
            </div>

            <!-- Fullscreen Canvas -->
            <div class="canvas-container">
                <canvas id="drawing-canvas"></canvas>
            </div>

            <!-- Bottom Actions -->
            <footer class="drawing-footer">
                <button type="button" id="btn-clear-canvas" class="btn-secondary">CLEAR</button>
                <button type="button" id="btn-done-drawing" class="btn-success">DONE</button>
            </footer>
        </section>

        <!-- 4. RESULT SCREEN -->
        <section id="screen-result" class="screen hidden">
            <header class="screen-header">
                <span class="brand-title">EXPONIT LABS</span>
                <button type="button" class="icon-btn btn-mute-toggle" aria-label="Toggle Sound">🔊</button>
            </header>
            <div class="result-card">
                <h2 id="result-status-title" class="result-title text-success">I GOT IT!</h2>
                <p id="result-subtitle" class="result-subtitle">Nice drawing! You couldn't fool me.</p>

                <div class="result-preview">
                    <span id="result-emoji" class="result-emoji">🚗</span>
                    <span id="result-guess-label" class="result-guess">CAR</span>
                    <span id="result-meta-info" class="result-meta">Confidence: 86%</span>
                </div>

                <div class="result-actions">
                    <button type="button" id="btn-replay-same" class="btn-secondary">DRAW AGAIN</button>
                    <button type="button" id="btn-replay-new" class="btn-primary">NEW CHALLENGE</button>
                </div>
            </div>
        </section>

        {{-- <!-- Feedback Toast -->
        <div id="ui-toast" class="ui-toast hidden"></div> --}}

        <!-- Debug Diagnostics Overlay -->
        <div id="debug-overlay" class="hidden"></div>
    </div>

    <!-- Local Bundled TensorFlow.js Runtime (Zero CDN dependency) -->
    <script src="{{ asset('lab/draw/js/tf.min.js') }}"></script>

    <!-- ES Module App Entrypoint -->
    <script type="module" src="{{ asset('lab/draw/js/app.js') }}"></script>
</body>

</html>
