// Admin-only: compiles an uploaded marker image into a MindAR ".mind" tracking
// file, entirely in the admin's browser. Nothing is sent to any external
// service — the resulting file is posted back to our own Laravel endpoint.
//
// It also scores how *trackable* the image is, using three real signals:
//   1. Contrast       — luminance std-dev of the image (kills black/flat images).
//   2. Feature count  — MindAR's detected tracking points on the base keyframe.
//   3. Coverage       — how many cells of a 5×5 grid actually contain features
//                       (kills images whose only detail is clustered in one spot).
// These fold into a 0–100 quality. A "Poor" image cannot be published.
//
// The heavy MindAR/TensorFlow bundle is loaded on demand (dynamic import) only
// when the admin clicks Compile, so it never weighs down the edit page.

const GRID = 5; // 5×5 = 25 coverage cells

const root = document.getElementById("ar-compiler");

if (root) {
    const button = root.querySelector("[data-compile]");
    const statusEl = root.querySelector("[data-status]");
    const verdictEl = root.querySelector("[data-verdict]");
    const bar = root.querySelector("[data-bar]");
    const barWrap = root.querySelector("[data-bar-wrap]");
    const preview = root.querySelector("[data-preview]");

    const setStatus = (text) => {
        if (statusEl) statusEl.textContent = text;
    };
    const setProgress = (pct) => {
        if (bar) bar.style.width = `${Math.round(pct)}%`;
    };

    const loadImage = (src) =>
        new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => resolve(img);
            img.onerror = () =>
                reject(new Error("Could not load the marker image."));
            img.src = src;
        });

    // Luminance standard deviation on a downscaled copy. ~0 for a flat/black image.
    const imageContrast = (image) => {
        const scale = Math.min(1, 256 / Math.max(image.width, image.height));
        const w = Math.max(1, Math.round(image.width * scale));
        const h = Math.max(1, Math.round(image.height * scale));
        const canvas = document.createElement("canvas");
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext("2d", { willReadFrequently: true });
        ctx.drawImage(image, 0, 0, w, h);
        const { data } = ctx.getImageData(0, 0, w, h);
        let sum = 0;
        let sumSq = 0;
        const n = w * h;
        for (let i = 0; i < data.length; i += 4) {
            const lum =
                0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            sum += lum;
            sumSq += lum * lum;
        }
        const mean = sum / n;
        return Math.sqrt(Math.max(0, sumSq / n - mean * mean));
    };

    // Pull the densest (highest-resolution) keyframe and measure its features.
    const analyseFeatures = (dataList) => {
        const keyframes = dataList?.[0]?.matchingData ?? [];
        let base = null;
        for (const kf of keyframes) {
            if (!base || kf.width * kf.height > base.width * base.height)
                base = kf;
        }
        if (!base) return { count: 0, coverage: 0, base: null, points: [] };

        const points = [
            ...(base.maximaPoints ?? []),
            ...(base.minimaPoints ?? []),
        ];
        const cells = new Set();
        for (const p of points) {
            const cx = Math.min(
                GRID - 1,
                Math.max(0, Math.floor((p.x / base.width) * GRID)),
            );
            const cy = Math.min(
                GRID - 1,
                Math.max(0, Math.floor((p.y / base.height) * GRID)),
            );
            cells.add(cy * GRID + cx);
        }
        return {
            count: points.length,
            coverage: cells.size / (GRID * GRID),
            base,
            points,
        };
    };

    // Paint the image with the 5×5 grid and the real tracking points on top.
    const drawPreview = (image, base, points) => {
        if (!preview) return;
        const scale = Math.min(1, 320 / Math.max(image.width, image.height));
        const w = Math.round(image.width * scale);
        const h = Math.round(image.height * scale);
        preview.width = w;
        preview.height = h;
        const ctx = preview.getContext("2d");
        ctx.drawImage(image, 0, 0, w, h);

        ctx.strokeStyle = "rgba(255,255,255,.35)";
        ctx.lineWidth = 1;
        for (let i = 1; i < GRID; i++) {
            ctx.beginPath();
            ctx.moveTo((w * i) / GRID, 0);
            ctx.lineTo((w * i) / GRID, h);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(0, (h * i) / GRID);
            ctx.lineTo(w, (h * i) / GRID);
            ctx.stroke();
        }

        if (base) {
            ctx.fillStyle = "rgba(16,185,129,.9)";
            for (const p of points) {
                const x = (p.x / base.width) * w;
                const y = (p.y / base.height) * h;
                ctx.beginPath();
                ctx.arc(x, y, 1.6, 0, Math.PI * 2);
                ctx.fill();
            }
        }
        preview.classList.remove("ar-c-hidden");
    };

    const tierOf = (score) =>
        score >= 70 ? "good" : score >= 45 ? "fair" : "poor";

    button?.addEventListener("click", async () => {
        button.disabled = true;
        verdictEl?.classList.add("ar-c-hidden");
        barWrap?.classList.remove("ar-c-hidden");
        setProgress(0);

        try {
            setStatus("Loading marker image…");
            const image = await loadImage(root.dataset.marker);

            const contrast = imageContrast(image);

            setStatus("Loading AR engine…");
            const { Compiler } =
                await import("mind-ar/dist/mindar-image.prod.js");

            setStatus("Analysing image for trackable features…");
            const compiler = new Compiler();
            const dataList = await compiler.compileImageTargets(
                [image],
                (progress) => setProgress(progress),
            );

            const { count, coverage, base, points } = analyseFeatures(dataList);

            // Fold the three signals into a 0–100 quality.
            const covScore = Math.min(1, coverage / 0.8); // 80% of cells = full marks
            const countScore = Math.min(1, count / 120);
            const contrastScore = Math.min(1, contrast / 45);
            let quality = Math.round(
                100 *
                    (0.45 * covScore + 0.3 * countScore + 0.25 * contrastScore),
            );

            // Hard floors: any one failing means the image won't track in practice.
            if (contrast < 10 || count < 30 || coverage < 0.28) {
                quality = Math.min(quality, 25);
            }
            quality = Math.max(0, Math.min(100, quality));
            const tier = tierOf(quality);

            drawPreview(image, base, points);

            if (verdictEl) {
                const messages = {
                    good: "✅ Good — should track reliably.",
                    fair: "⚠️ Fair — will track; keep the printed marker flat and well lit.",
                    poor: "⛔ Poor — this image won’t track and can’t be published. Use a detailed, high-contrast picture.",
                };
                verdictEl.textContent = `${messages[tier]}  ·  ${count} features · ${Math.round(coverage * 100)}% coverage · contrast ${Math.round(contrast)} · score ${quality}/100`;
                verdictEl.style.color =
                    tier === "good"
                        ? "rgb(16 185 129)"
                        : tier === "fair"
                          ? "rgb(202 138 4)"
                          : "rgb(220 38 38)";
                verdictEl.classList.remove("ar-c-hidden");
            }

            setStatus("Saving tracking file…");
            const buffer = await compiler.exportData();
            const form = new FormData();
            form.append("mind", new Blob([buffer]), "target.mind");
            form.append("tracking_score", String(quality));

            const response = await fetch(root.dataset.endpoint, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": root.dataset.csrf,
                    Accept: "application/json",
                },
                body: form,
            });

            if (!response.ok) {
                throw new Error("The server rejected the tracking file.");
            }

            barWrap?.classList.add("ar-c-hidden");
            setStatus(
                tier === "poor"
                    ? "Saved — but replace this image; it can’t be published."
                    : "Saved. You can publish this creative now.",
            );
            button.disabled = false;
        } catch (error) {
            setStatus(`Failed: ${error?.message ?? error}`);
            button.disabled = false;
        }
    });
}
