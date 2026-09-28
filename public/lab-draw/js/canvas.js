/**
 * High-DPI Smooth Pointer & Stylus Canvas for Exponit Draw
 * 
 * Features:
 * - High-DPI crisp rendering with ResizeObserver
 * - Stylus (pen), touch, and mouse support with pressure sensitivity
 * - Smooth round-capped strokes with zero latency
 * - Normalized stroke capture (0 -> 1 coordinates, timestamps, pressure)
 * - Robust bounding box calculation & 28x28 centered rasterization for AI
 */

export class DrawingCanvas {
    constructor(canvasElement, onStrokeUpdate = null) {
        this.canvas = canvasElement;
        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
        this.onStrokeUpdate = onStrokeUpdate;

        // Stroke storage
        this.strokes = []; // Array of { points: [{x, y, t, p}], pointerType }
        this.currentStroke = null;
        this.lastPoint = null;
        this.isDrawing = false;
        this.lastPointerType = 'unknown';

        // Styling
        this.strokeColor = '#0f172a'; // Deep slate
        this.baseLineWidth = 5.0;

        // Dimensions
        this.width = 0;
        this.height = 0;
        this.dpr = window.devicePixelRatio || 1;
        this.pointCount = 0;

        this.init();
    }

    init() {
        this.resize();
        window.addEventListener('resize', () => this.resize());

        // Observe element size changes (e.g. when unhidden from display:none)
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(() => {
                this.resize();
            });
            this.resizeObserver.observe(this.canvas);
            if (this.canvas.parentElement) {
                this.resizeObserver.observe(this.canvas.parentElement);
            }
        }

        // Attach Pointer Events
        this.canvas.addEventListener('pointerdown', (e) => this.handlePointerDown(e));
        this.canvas.addEventListener('pointermove', (e) => this.handlePointerMove(e));
        this.canvas.addEventListener('pointerup', (e) => this.handlePointerUp(e));
        this.canvas.addEventListener('pointercancel', (e) => this.handlePointerUp(e));
        this.canvas.addEventListener('pointerleave', (e) => this.handlePointerUp(e));

        // Prevent context menu
        this.canvas.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    resize() {
        const dpr = window.devicePixelRatio || 1;
        const rect = this.canvas.getBoundingClientRect();

        const width = Math.round(rect.width || this.canvas.clientWidth || (this.canvas.parentElement ? this.canvas.parentElement.clientWidth : 0));
        const height = Math.round(rect.height || this.canvas.clientHeight || (this.canvas.parentElement ? this.canvas.parentElement.clientHeight : 0));

        if (width === 0 || height === 0) return;

        // If dimensions haven't changed, don't re-scale
        if (this.width === width && this.height === height && this.dpr === dpr) {
            return;
        }

        this.dpr = dpr;
        this.width = width;
        this.height = height;

        // Set backing store dimensions
        this.canvas.width = Math.round(width * dpr);
        this.canvas.height = Math.round(height * dpr);

        // Reset transform and scale for DPR
        this.ctx.setTransform(1, 0, 0, 1, 0, 0);
        this.ctx.scale(dpr, dpr);

        this.redrawAll();
    }

    getCanvasWidth() {
        if (!this.width || this.width === 0) {
            this.resize();
        }
        return this.width || this.canvas.clientWidth || 800;
    }

    getCanvasHeight() {
        if (!this.height || this.height === 0) {
            this.resize();
        }
        return this.height || this.canvas.clientHeight || 500;
    }

    handlePointerDown(e) {
        if (e.button !== 0 && e.pointerType === 'mouse') return;

        // Ensure canvas geometry is fresh
        const canvasW = this.getCanvasWidth();
        const canvasH = this.getCanvasHeight();

        this.isDrawing = true;
        this.lastPointerType = e.pointerType;
        try {
            this.canvas.setPointerCapture(e.pointerId);
        } catch (_) {}

        const pos = this.getCanvasPosition(e);
        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;

        const normX = Math.max(0, Math.min(1, pos.x / canvasW));
        const normY = Math.max(0, Math.min(1, pos.y / canvasH));

        this.currentStroke = {
            points: [{
                x: normX,
                y: normY,
                t: Date.now(),
                p: pressure
            }],
            pointerType: e.pointerType
        };
        this.strokes.push(this.currentStroke);
        this.pointCount++;
        this.lastPoint = pos;

        // Draw start dot
        const dotRadius = Math.max(2.0, (this.baseLineWidth * (0.6 + pressure * 0.8)) / 2);
        this.ctx.beginPath();
        this.ctx.arc(pos.x, pos.y, dotRadius, 0, Math.PI * 2);
        this.ctx.fillStyle = this.strokeColor;
        this.ctx.fill();

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: this.strokes.length,
                pointCount: this.pointCount
            });
        }
    }

    handlePointerMove(e) {
        if (!this.isDrawing || !this.currentStroke || !this.lastPoint) return;

        const canvasW = this.getCanvasWidth();
        const canvasH = this.getCanvasHeight();

        const currentPos = this.getCanvasPosition(e);
        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;

        // Filter tiny micro-jitter (less than 1.5px)
        const dist = Math.hypot(currentPos.x - this.lastPoint.x, currentPos.y - this.lastPoint.y);
        if (dist < 1.5) return;

        // Record normalized point
        const normX = Math.max(0, Math.min(1, currentPos.x / canvasW));
        const normY = Math.max(0, Math.min(1, currentPos.y / canvasH));

        this.currentStroke.points.push({
            x: normX,
            y: normY,
            t: Date.now(),
            p: pressure
        });
        this.pointCount++;

        // Render solid round-capped smooth line
        this.ctx.beginPath();
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = this.strokeColor;
        this.ctx.lineWidth = Math.max(3.5, this.baseLineWidth * (0.6 + pressure * 0.8));

        this.ctx.moveTo(this.lastPoint.x, this.lastPoint.y);
        this.ctx.lineTo(currentPos.x, currentPos.y);
        this.ctx.stroke();

        this.lastPoint = currentPos;

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: this.strokes.length,
                pointCount: this.pointCount
            });
        }
    }

    handlePointerUp(e) {
        if (!this.isDrawing) return;
        this.isDrawing = false;
        try {
            if (this.canvas.hasPointerCapture(e.pointerId)) {
                this.canvas.releasePointerCapture(e.pointerId);
            }
        } catch (_) {}

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: this.strokes.length,
                pointCount: this.pointCount
            });
        }
    }

    getCanvasPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: Math.max(0, Math.min(rect.width, e.clientX - rect.left)),
            y: Math.max(0, Math.min(rect.height, e.clientY - rect.top))
        };
    }

    redrawAll() {
        const w = this.getCanvasWidth();
        const h = this.getCanvasHeight();

        this.ctx.clearRect(0, 0, w, h);
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = this.strokeColor;

        for (const stroke of this.strokes) {
            if (!stroke.points || stroke.points.length === 0) continue;

            const pts = stroke.points.filter(p => Number.isFinite(p.x) && Number.isFinite(p.y));
            if (pts.length === 0) continue;

            if (pts.length === 1) {
                const pressure = pts[0].p || 0.5;
                const dotRadius = Math.max(2.0, (this.baseLineWidth * (0.6 + pressure * 0.8)) / 2);
                this.ctx.beginPath();
                this.ctx.arc(pts[0].x * w, pts[0].y * h, dotRadius, 0, Math.PI * 2);
                this.ctx.fillStyle = this.strokeColor;
                this.ctx.fill();
                continue;
            }

            for (let i = 1; i < pts.length; i++) {
                const pressure = pts[i].p || 0.5;
                this.ctx.beginPath();
                this.ctx.lineWidth = Math.max(3.5, this.baseLineWidth * (0.6 + pressure * 0.8));
                this.ctx.moveTo(pts[i - 1].x * w, pts[i - 1].y * h);
                this.ctx.lineTo(pts[i].x * w, pts[i].y * h);
                this.ctx.stroke();
            }
        }
    }

    clear() {
        this.strokes = [];
        this.currentStroke = null;
        this.lastPoint = null;
        this.pointCount = 0;
        const w = this.getCanvasWidth();
        const h = this.getCanvasHeight();
        this.ctx.clearRect(0, 0, w, h);

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: 0,
                pointCount: 0
            });
        }
    }

    isEmpty() {
        return !this.strokes || this.strokes.length === 0 || this.pointCount < 5;
    }

    /**
     * Preprocesses canvas into 28x28 centered bitmap for TensorFlow.js
     * Format: Inverted (black background 0.0, white stroke 1.0)
     */
    getPreprocessedCanvas(size = 28) {
        if (this.isEmpty()) return null;

        const w = this.getCanvasWidth();
        const h = this.getCanvasHeight();

        // 1. Calculate bounding box directly from points
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        let validPointCount = 0;

        for (const stroke of this.strokes) {
            for (const p of stroke.points) {
                if (Number.isFinite(p.x) && Number.isFinite(p.y)) {
                    const px = p.x * w;
                    const py = p.y * h;
                    minX = Math.min(minX, px);
                    minY = Math.min(minY, py);
                    maxX = Math.max(maxX, px);
                    maxY = Math.max(maxY, py);
                    validPointCount++;
                }
            }
        }

        if (validPointCount < 5 || minX === Infinity) return null;

        const offscreen = document.createElement('canvas');
        offscreen.width = size;
        offscreen.height = size;
        const offCtx = offscreen.getContext('2d');

        // Black background
        offCtx.fillStyle = '#000000';
        offCtx.fillRect(0, 0, size, size);

        const pad = 3;
        const drawW = Math.max(1, maxX - minX);
        const drawH = Math.max(1, maxY - minY);
        const maxDim = Math.max(drawW, drawH, 10);
        const scale = (size - pad * 2) / maxDim;

        const offsetX = (size - drawW * scale) / 2;
        const offsetY = (size - drawH * scale) / 2;

        offCtx.save();
        offCtx.translate(offsetX - minX * scale, offsetY - minY * scale);
        offCtx.scale(scale, scale);

        offCtx.lineCap = 'round';
        offCtx.lineJoin = 'round';
        offCtx.strokeStyle = '#ffffff';
        offCtx.lineWidth = Math.max(1.8 / scale, 2.2 / scale);

        for (const stroke of this.strokes) {
            const pts = stroke.points.filter(p => Number.isFinite(p.x) && Number.isFinite(p.y));
            if (pts.length === 0) continue;

            const startX = pts[0].x * w;
            const startY = pts[0].y * h;

            if (pts.length === 1) {
                offCtx.beginPath();
                offCtx.arc(startX, startY, offCtx.lineWidth / 2, 0, Math.PI * 2);
                offCtx.fillStyle = '#ffffff';
                offCtx.fill();
                continue;
            }

            offCtx.beginPath();
            offCtx.moveTo(startX, startY);
            for (let i = 1; i < pts.length; i++) {
                offCtx.lineTo(pts[i].x * w, pts[i].y * h);
            }
            offCtx.stroke();
        }
        offCtx.restore();

        return offscreen;
    }
}
