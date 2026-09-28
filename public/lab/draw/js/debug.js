/**
 * Debug Diagnostics Overlay for Exponit Draw
 * Activated via `?debug=1` in URL or triple-tapping the top header
 */
export class DebugPanel {
    constructor(element) {
        this.element = element;
        this.isVisible = new URLSearchParams(window.location.search).get('debug') === '1';
        this.metrics = {
            state: 'BOOT',
            challenge: 'none',
            classifier: 'MockClassifier',
            inferenceCount: 0,
            lastLatency: 0,
            pointerType: 'none',
            strokes: 0,
            points: 0,
            dpr: window.devicePixelRatio || 1,
            viewport: `${window.innerWidth}x${window.innerHeight}`,
            online: navigator.onLine ? 'YES' : 'NO'
        };

        if (this.isVisible) {
            this.show();
        }

        window.addEventListener('online', () => this.update({ online: 'YES' }));
        window.addEventListener('offline', () => this.update({ online: 'NO' }));
        window.addEventListener('resize', () => this.update({
            viewport: `${window.innerWidth}x${window.innerHeight}`
        }));
    }

    toggle() {
        this.isVisible = !this.isVisible;
        if (this.isVisible) {
            this.show();
        } else {
            this.hide();
        }
    }

    show() {
        if (!this.element) return;
        this.element.classList.remove('hidden');
        this.render();
    }

    hide() {
        if (!this.element) return;
        this.element.classList.add('hidden');
    }

    update(partial) {
        Object.assign(this.metrics, partial);
        if (this.isVisible) {
            this.render();
        }
    }

    render() {
        if (!this.element) return;
        this.element.innerHTML = `
            <div class="debug-card">
                <div class="debug-header">
                    <span>⚡ EXPONIT DRAW DEBUG</span>
                    <button class="debug-close" id="debug-close-btn">&times;</button>
                </div>
                <div class="debug-grid">
                    <div><span>State:</span> <b>${this.metrics.state}</b></div>
                    <div><span>Target:</span> <b>${this.metrics.challenge}</b></div>
                    <div><span>Engine:</span> <b class="text-amber">${this.metrics.classifier}</b></div>
                    <div><span>Inferences:</span> <b>${this.metrics.inferenceCount}</b></div>
                    <div><span>Latency:</span> <b>${this.metrics.lastLatency} ms</b></div>
                    <div><span>Pointer:</span> <b>${this.metrics.pointerType}</b></div>
                    <div><span>Strokes:</span> <b>${this.metrics.strokes}</b></div>
                    <div><span>Points:</span> <b>${this.metrics.points}</b></div>
                    <div><span>Display:</span> <b>${this.metrics.viewport} (${this.metrics.dpr}x)</b></div>
                    <div><span>Online:</span> <b class="${this.metrics.online === 'YES' ? 'text-green' : 'text-rose'}">${this.metrics.online}</b></div>
                </div>
            </div>
        `;

        const closeBtn = document.getElementById('debug-close-btn');
        if (closeBtn) {
            closeBtn.onclick = () => this.toggle();
        }
    }
}
