/**
 * High-DPI Smooth Pointer & Stylus Canvas for Exponit Draw
 * 
 * Features:
 * - High-DPI crisp rendering
 * - Stylus (pen), touch, and mouse support
 * - Quadratic Bezier curve smoothing for natural pen feel
 * - Normalized stroke capture (0 -> 1 coordinates, timestamps, pressure)
 * - Bounding box calculation & rasterization helper for AI preprocessing
 */

export class DrawingCanvas {
    constructor(canvasElement, onStrokeUpdate = null) {
        this.canvas = canvasElement;
        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
        this.onStrokeUpdate = onStrokeUpdate;

        // Stroke storage
        this.strokes = []; // Array of { points: [{x, y, t, p}] }
        this.currentStroke = null;
        this.lastPoint = null;
        this.isDrawing = false;
        this.lastPointerType = 'unknown';

        // Styling
        this.strokeColor = '#0f172a'; // Deep slate
        this.baseLineWidth = 5.0;

        // Bounding box cache
        this.bounds = { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity };
        this.pointCount = 0;

        this.init();
    }

    init() {
        this.resize();
        window.addEventListener('resize', () => this.resize());

        // Attach Pointer Events
        this.canvas.addEventListener('pointerdown', (e) => this.handlePointerDown(e));
        this.canvas.addEventListener('pointermove', (e) => this.handlePointerMove(e));
        this.canvas.addEventListener('pointerup', (e) => this.handlePointerUp(e));
        this.canvas.addEventListener('pointercancel', (e) => this.handlePointerUp(e));
        this.canvas.addEventListener('pointerleave', (e) => this.handlePointerUp(e));

        // Prevent context menu on long press
        this.canvas.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    resize() {
        const dpr = window.devicePixelRatio || 1;
        const rect = this.canvas.getBoundingClientRect();

        if (rect.width === 0 || rect.height === 0) return;

        // Set backing store dimensions
        this.canvas.width = Math.round(rect.width * dpr);
        this.canvas.height = Math.round(rect.height * dpr);

        // Normalize coordinate system
        this.ctx.scale(dpr, dpr);

        this.dpr = dpr;
        this.width = rect.width;
        this.height = rect.height;

        this.redrawAll();
    }

    handlePointerDown(e) {
        if (e.button !== 0 && e.pointerType === 'mouse') return;

        this.isDrawing = true;
        this.lastPointerType = e.pointerType;
        this.canvas.setPointerCapture(e.pointerId);

        const pos = this.getCanvasPosition(e);
        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;

        this.currentStroke = {
            points: [{
                x: pos.x / this.width,
                y: pos.y / this.height,
                t: Date.now(),
                p: pressure
            }],
            pointerType: e.pointerType
        };
        this.strokes.push(this.currentStroke);
        this.pointCount++;

        this.updateBounds(pos.x, pos.y);
        this.lastPoint = pos;

        // Draw start dot
        this.ctx.beginPath();
        this.ctx.arc(pos.x, pos.y, (this.baseLineWidth * pressure) / 2, 0, Math.PI * 2);
        this.ctx.fillStyle = this.strokeColor;
        this.ctx.fill();
    }

    handlePointerMove(e) {
        if (!this.isDrawing || !this.currentStroke) return;

        const currentPos = this.getCanvasPosition(e);
        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;

        // Filter tiny micro-movements
        const dist = Math.hypot(currentPos.x - this.lastPoint.x, currentPos.y - this.lastPoint.y);
        if (dist < 2) return;

        // Record normalized point
        this.currentStroke.points.push({
            x: currentPos.x / this.width,
            y: currentPos.y / this.height,
            t: Date.now(),
            p: pressure
        });
        this.pointCount++;
        this.updateBounds(currentPos.x, currentPos.y);

        // Smooth Bezier Curve Drawing
        this.ctx.beginPath();
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = this.strokeColor;
        this.ctx.lineWidth = Math.max(3.0, this.baseLineWidth * (0.6 + pressure * 0.8));

        const midPoint = {
            x: (this.lastPoint.x + currentPos.x) / 2,
            y: (this.lastPoint.y + currentPos.y) / 2
        };

        this.ctx.moveTo(this.lastPoint.x, this.lastPoint.y);
        this.ctx.quadraticCurveTo(this.lastPoint.x, this.lastPoint.y, midPoint.x, midPoint.y);
        this.ctx.stroke();

        this.lastPoint = currentPos;

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: this.strokes.length,
                pointCount: this.pointCount,
                bounds: this.bounds
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
                pointCount: this.pointCount,
                bounds: this.bounds
            });
        }
    }

    getCanvasPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    updateBounds(x, y) {
        this.bounds.minX = Math.min(this.bounds.minX, x);
        this.bounds.minY = Math.min(this.bounds.minY, y);
        this.bounds.maxX = Math.max(this.bounds.maxX, x);
        this.bounds.maxY = Math.max(this.bounds.maxY, y);
    }

    redrawAll() {
        this.ctx.clearRect(0, 0, this.width, this.height);

        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = this.strokeColor;

        for (const stroke of this.strokes) {
            if (stroke.points.length === 0) continue;

            const pts = stroke.points.map(p => ({
                x: p.x * this.width,
                y: p.y * this.height,
                p: p.p || 0.5
            }));

            if (pts.length === 1) {
                this.ctx.beginPath();
                this.ctx.arc(pts[0].x, pts[0].y, (this.baseLineWidth * pts[0].p) / 2, 0, Math.PI * 2);
                this.ctx.fillStyle = this.strokeColor;
                this.ctx.fill();
                continue;
            }

            for (let i = 1; i < pts.length; i++) {
                this.ctx.beginPath();
                this.ctx.lineWidth = Math.max(3.0, this.baseLineWidth * (0.6 + pts[i].p * 0.8));
                this.ctx.moveTo(pts[i - 1].x, pts[i - 1].y);
                const mid = {
                    x: (pts[i - 1].x + pts[i].x) / 2,
                    y: (pts[i - 1].y + pts[i].y) / 2
                };
                this.ctx.quadraticCurveTo(pts[i - 1].x, pts[i - 1].y, mid.x, mid.y);
                this.ctx.stroke();
            }
        }
    }

    clear() {
        this.strokes = [];
        this.currentStroke = null;
        this.lastPoint = null;
        this.pointCount = 0;
        this.bounds = { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity };
        this.ctx.clearRect(0, 0, this.width, this.height);

        if (this.onStrokeUpdate) {
            this.onStrokeUpdate({
                strokeCount: 0,
                pointCount: 0,
                bounds: this.bounds
            });
        }
    }

    isEmpty() {
        return this.strokes.length === 0 || this.pointCount < 5;
    }

    /**
     * Extracts normalized centered bitmap crop ready for AI model input (28x28 or 64x64)
     */
    getNormalizedImageData(size = 28) {
        if (this.isEmpty()) return null;

        const offscreen = document.createElement('canvas');
        offscreen.width = size;
        offscreen.height = size;
        const offCtx = offscreen.getContext('2d');

        // Black background, white drawing (standard QuickDraw format)
        offCtx.fillStyle = '#000000';
        offCtx.fillRect(0, 0, size, size);

        const pad = 4;
        const drawW = this.bounds.maxX - this.bounds.minX;
        const drawH = this.bounds.maxY - this.bounds.minY;
        const maxDim = Math.max(drawW, drawH, 10);
        const scale = (size - pad * 2) / maxDim;

        const offsetX = (size - drawW * scale) / 2;
        const offsetY = (size - drawH * scale) / 2;

        offCtx.save();
        offCtx.translate(offsetX - this.bounds.minX * scale, offsetY - this.bounds.minY * scale);
        offCtx.scale(scale, scale);

        offCtx.lineCap = 'round';
        offCtx.lineJoin = 'round';
        offCtx.strokeStyle = '#ffffff';
        offCtx.lineWidth = 14 / scale; // Normalized line thickness for 28x28

        for (const stroke of this.strokes) {
            if (stroke.points.length < 2) continue;
            offCtx.beginPath();
            offCtx.moveTo(stroke.points[0].x * this.width, stroke.points[0].y * this.height);
            for (let i = 1; i < stroke.points.length; i++) {
                offCtx.lineTo(stroke.points[i].x * this.width, stroke.points[i].y * this.height);
            }
            offCtx.stroke();
        }
        offCtx.restore();

        return offCtx.getImageData(0, 0, size, size);
    }
}
