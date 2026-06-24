{{--
    Self-contained error page with a tiny "pill runner" game.
    No @vite / Alpine / DB / route() — must render even in maintenance mode (503)
    or when assets are unavailable. Everything is inlined.
    Expects: $code, $heading, $sub
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="robots" content="noindex">
    <title>{{ $code }} — Exponit Labs</title>
    <style>
        :root { --ink:#0f2a44; --brand:#1fb6aa; --light:#84fff2; }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(60% 60% at 80% 15%, rgba(31,182,170,.18), transparent 60%), var(--ink);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            min-height: 100%; padding: 24px; text-align: center;
            -webkit-user-select: none; user-select: none;
        }
        .wrap { width: 100%; max-width: 540px; }
        .badge {
            display: inline-block; font-size: 12px; letter-spacing: .18em; text-transform: uppercase;
            color: var(--light); border: 1px solid rgba(255,255,255,.2); border-radius: 999px;
            padding: 6px 14px; margin-bottom: 20px;
        }
        h1 { font-size: clamp(64px, 22vw, 120px); margin: 0; line-height: 1; font-weight: 800; letter-spacing: -.04em; }
        .msg { font-size: clamp(18px, 5vw, 24px); font-weight: 600; margin: 12px 0 4px; }
        .sub { color: rgba(255,255,255,.6); margin: 0 0 22px; font-size: 14px; }
        .game {
            position: relative; background: #f4f7fa; border-radius: 16px; overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,.35); touch-action: manipulation;
        }
        canvas { display: block; width: 100%; height: 180px; }
        #hint {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            color: #0f2a44; font-weight: 600; font-size: 14px; background: rgba(244,247,250,.6); cursor: pointer;
        }
        #hint.hide { display: none; }
        .score { margin-top: 12px; font-size: 13px; color: rgba(255,255,255,.65); }
        .score span { color: #fff; font-weight: 700; }
        .home {
            display: inline-block; margin-top: 22px; color: var(--ink); background: var(--brand);
            text-decoration: none; font-weight: 600; padding: 12px 22px; border-radius: 14px;
        }
        .home:hover { background: var(--light); }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="badge">Exponit Labs</div>
        <h1>{{ $code }}</h1>
        <p class="msg">{{ $heading }}</p>
        <p class="sub">{{ $sub }} While you’re here — jump the germs.</p>

        <div class="game" id="stage">
            <canvas id="g"></canvas>
            <div id="hint">Tap / Space to jump</div>
        </div>

        <div class="score">Score <span id="score">0</span> · Best <span id="best">0</span></div>
        <br>
        <a class="home" href="/">← Back to home</a>
    </div>

    <script>
        (function () {
            var canvas = document.getElementById('g'),
                ctx = canvas.getContext('2d'),
                stage = document.getElementById('stage'),
                hint = document.getElementById('hint'),
                scoreEl = document.getElementById('score'),
                bestEl = document.getElementById('best');

            var H = 180, W = 0, dpr = Math.max(1, window.devicePixelRatio || 1);
            var ground = H - 28;

            function resize() {
                W = stage.clientWidth;
                canvas.width = W * dpr;
                canvas.height = H * dpr;
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            }
            window.addEventListener('resize', resize);
            resize();

            var best = +(localStorage.getItem('exl_run_best') || 0);
            bestEl.textContent = best;

            var player, obstacles, speed, score, gravity, running, started, spawn;

            function reset() {
                player = { x: 48, y: ground, w: 22, h: 34, vy: 0, onGround: true };
                obstacles = [];
                speed = 3.4;
                score = 0;
                gravity = 0.7;
                running = true;
                spawn = 0;
            }

            function jump() {
                if (!started) { started = true; hint.classList.add('hide'); reset(); loop(); return; }
                if (!running) { reset(); loop(); return; }
                if (player.onGround) { player.vy = -11; player.onGround = false; }
            }

            function drawPill(x, y, w, h) {
                var r = w / 2;
                // navy half
                ctx.fillStyle = '#0f2a44';
                ctx.beginPath();
                ctx.arc(x + r, y - h + r, r, Math.PI, 0); ctx.lineTo(x + w, y - r); ctx.lineTo(x, y - r); ctx.closePath(); ctx.fill();
                // teal half
                ctx.fillStyle = '#1fb6aa';
                ctx.beginPath();
                ctx.arc(x + r, y - r, r, 0, Math.PI); ctx.lineTo(x, y - r); ctx.lineTo(x + w, y - r); ctx.closePath(); ctx.fill();
                ctx.fillStyle = 'rgba(255,255,255,.6)';
                ctx.fillRect(x + 4, y - h + 6, 3, h - 12);
            }

            function loop() {
                if (!running) {
                    ctx.fillStyle = 'rgba(15,42,68,.06)'; ctx.fillRect(0, 0, W, H);
                    ctx.fillStyle = '#0f2a44'; ctx.font = '600 16px ui-sans-serif, system-ui';
                    ctx.textAlign = 'center';
                    ctx.fillText('Game over — tap / space to retry', W / 2, H / 2);
                    return;
                }
                requestAnimationFrame(loop);
                ctx.clearRect(0, 0, W, H);

                // ground
                ctx.strokeStyle = '#cdd7e1'; ctx.lineWidth = 2;
                ctx.beginPath(); ctx.moveTo(0, ground); ctx.lineTo(W, ground); ctx.stroke();

                // player physics
                player.vy += gravity; player.y += player.vy;
                if (player.y >= ground) { player.y = ground; player.vy = 0; player.onGround = true; }
                drawPill(player.x, player.y, player.w, player.h);

                // obstacles
                spawn--;
                if (spawn <= 0) {
                    var gap = 70 + Math.random() * 60;
                    obstacles.push({ x: W + 10, w: 14 + Math.random() * 12, h: 18 + Math.random() * 20 });
                    spawn = (90 + Math.random() * 40) / (speed / 3.4);
                }
                ctx.fillStyle = '#e4572e';
                for (var i = obstacles.length - 1; i >= 0; i--) {
                    var o = obstacles[i];
                    o.x -= speed;
                    // little germ: circle
                    ctx.beginPath(); ctx.arc(o.x + o.w / 2, ground - o.h / 2, o.h / 2, 0, Math.PI * 2); ctx.fill();
                    // collision (AABB-ish)
                    if (player.x < o.x + o.w && player.x + player.w > o.x &&
                        player.y > ground - o.h) {
                        running = false;
                        best = Math.max(best, Math.floor(score));
                        localStorage.setItem('exl_run_best', best);
                        bestEl.textContent = best;
                    }
                    if (o.x + o.w < 0) obstacles.splice(i, 1);
                }

                score += speed * 0.05;
                speed += 0.0016;
                scoreEl.textContent = Math.floor(score);
            }

            // controls
            window.addEventListener('keydown', function (e) {
                if (e.code === 'Space' || e.code === 'ArrowUp') { e.preventDefault(); jump(); }
            });
            stage.addEventListener('pointerdown', function (e) { e.preventDefault(); jump(); }, { passive: false });
        })();
    </script>
</body>

</html>
