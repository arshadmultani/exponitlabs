/**
 * Telestrator Drawing Engine
 * 
 * Provides an interactive inking layer over the 16:9 presentation slide.
 * Supports stylus/pen, touch, and mouse input with High-DPI (Retina) calibration,
 * undo history, and slide-specific stroke persistence.
 */
export class Telestrator {
  constructor(canvasElement) {
    this.canvas = canvasElement;
    this.ctx = canvasElement.getContext('2d');
    
    this.isEnabled = false;
    this.isDrawing = false;
    this.tool = 'pen'; // 'pen' | 'highlighter' | 'eraser'
    this.color = '#1FB6AA'; // Default Brand Teal
    this.penSize = 3.5;
    this.highlighterSize = 22;
    this.eraserSize = 28;

    this.slideDrawings = new Map(); // slideIndex -> ImageData
    this.currentSlideIndex = 0;
    this.history = []; // Array of ImageData for undo per current slide
    this.maxHistory = 15;

    this.lastX = 0;
    this.lastY = 0;

    this._initCanvas();
    this._attachEvents();
  }

  _initCanvas() {
    this.resize();
    window.addEventListener('resize', () => this.resize());
  }

  resize() {
    const rect = this.canvas.parentElement.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) return;

    const dpr = window.devicePixelRatio || 1;

    // Save current content before resize
    let savedData = null;
    if (this.canvas.width > 0 && this.canvas.height > 0) {
      try {
        savedData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
      } catch (e) {
        // ignore
      }
    }

    this.canvas.width = rect.width * dpr;
    this.canvas.height = rect.height * dpr;
    this.canvas.style.width = `${rect.width}px`;
    this.canvas.style.height = `${rect.height}px`;

    this.ctx.scale(dpr, dpr);
    this.ctx.lineCap = 'round';
    this.ctx.lineJoin = 'round';

    if (savedData) {
      this.ctx.putImageData(savedData, 0, 0);
    }
  }

  _attachEvents() {
    this.canvas.addEventListener('pointerdown', (e) => this._onPointerDown(e));
    this.canvas.addEventListener('pointermove', (e) => this._onPointerMove(e));
    this.canvas.addEventListener('pointerup', () => this._onPointerUp());
    this.canvas.addEventListener('pointercancel', () => this._onPointerUp());
  }

  _getCoordinates(e) {
    const rect = this.canvas.getBoundingClientRect();
    return {
      x: e.clientX - rect.left,
      y: e.clientY - rect.top
    };
  }

  _pushHistory() {
    try {
      if (this.history.length >= this.maxHistory) {
        this.history.shift();
      }
      this.history.push(this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height));
    } catch (e) {
      // ignore
    }
  }

  _onPointerDown(e) {
    if (!this.isEnabled) return;
    e.preventDefault();
    this.isDrawing = true;

    this._pushHistory();

    const { x, y } = this._getCoordinates(e);
    this.lastX = x;
    this.lastY = y;

    this.ctx.beginPath();
    this.ctx.moveTo(x, y);
    this.ctx.lineTo(x, y);

    this._applyToolSettings();
    this.ctx.stroke();
  }

  _onPointerMove(e) {
    if (!this.isDrawing || !this.isEnabled) return;
    e.preventDefault();

    const { x, y } = this._getCoordinates(e);

    this.ctx.beginPath();
    this.ctx.moveTo(this.lastX, this.lastY);
    this.ctx.lineTo(x, y);

    this._applyToolSettings();
    this.ctx.stroke();

    this.lastX = x;
    this.lastY = y;
  }

  _onPointerUp() {
    if (!this.isDrawing) return;
    this.isDrawing = false;
    this.saveForSlide(this.currentSlideIndex);
  }

  _applyToolSettings() {
    if (this.tool === 'highlighter') {
      this.ctx.globalCompositeOperation = 'source-over';
      // Semi-transparent alpha for highlighter effect
      this.ctx.strokeStyle = this._hexToRgba(this.color, 0.4);
      this.ctx.lineWidth = this.highlighterSize;
    } else if (this.tool === 'eraser') {
      this.ctx.globalCompositeOperation = 'destination-out';
      this.ctx.strokeStyle = 'rgba(0,0,0,1)';
      this.ctx.lineWidth = this.eraserSize;
    } else {
      // Normal solid pen
      this.ctx.globalCompositeOperation = 'source-over';
      this.ctx.strokeStyle = this.color;
      this.ctx.lineWidth = this.penSize;
    }
  }

  _hexToRgba(hex, alpha) {
    let c = hex.replace('#', '');
    if (c.length === 3) {
      c = c.split('').map(x => x + x).join('');
    }
    const num = parseInt(c, 16);
    const r = (num >> 16) & 255;
    const g = (num >> 8) & 255;
    const b = num & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }

  setTool(tool) {
    this.tool = tool;
  }

  setColor(hex) {
    this.color = hex;
  }

  toggle(enabled) {
    this.isEnabled = enabled !== undefined ? enabled : !this.isEnabled;
    this.canvas.style.pointerEvents = this.isEnabled ? 'auto' : 'none';
    return this.isEnabled;
  }

  clear() {
    const rect = this.canvas.getBoundingClientRect();
    this._pushHistory();
    this.ctx.clearRect(0, 0, rect.width, rect.height);
    this.slideDrawings.delete(this.currentSlideIndex);
  }

  undo() {
    if (this.history.length > 0) {
      const prev = this.history.pop();
      this.ctx.putImageData(prev, 0, 0);
      this.saveForSlide(this.currentSlideIndex);
    }
  }

  saveForSlide(slideIndex) {
    try {
      const data = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
      this.slideDrawings.set(slideIndex, data);
    } catch (e) {
      // ignore
    }
  }

  loadForSlide(slideIndex) {
    this.currentSlideIndex = slideIndex;
    this.history = [];
    
    const rect = this.canvas.getBoundingClientRect();
    this.ctx.clearRect(0, 0, rect.width, rect.height);

    if (this.slideDrawings.has(slideIndex)) {
      const data = this.slideDrawings.get(slideIndex);
      this.ctx.putImageData(data, 0, 0);
    }
  }
}
