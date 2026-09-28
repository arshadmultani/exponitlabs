/**
 * Web Audio API Synthesizer for Exponit Draw
 * 100% offline, zero audio asset downloads, ultra-low latency.
 */
class SoundEffects {
    constructor() {
        this.ctx = null;
        this.muted = localStorage.getItem('exponit_draw_muted') === 'true';
    }

    init() {
        if (!this.ctx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                this.ctx = new AudioContextClass();
            }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume().catch(() => {});
        }
    }

    toggleMute() {
        this.muted = !this.muted;
        localStorage.setItem('exponit_draw_muted', this.muted);
        return this.muted;
    }

    isMuted() {
        return this.muted;
    }

    playTone(frequency, type = 'sine', duration = 0.08, gainVal = 0.12, decay = 0.06) {
        if (this.muted) return;
        this.init();
        if (!this.ctx) return;

        try {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(frequency, this.ctx.currentTime);

            gain.gain.setValueAtTime(gainVal, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + decay);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch (e) {
            // Audio error silently ignored
        }
    }

    tick() {
        // Subtle clock tick
        this.playTone(800, 'triangle', 0.03, 0.08, 0.03);
    }

    countdownGo() {
        // Crispy pleasant chord for "GO"
        if (this.muted) return;
        this.init();
        if (!this.ctx) return;

        [523.25, 659.25, 783.99].forEach((freq, i) => {
            setTimeout(() => {
                this.playTone(freq, 'sine', 0.15, 0.1, 0.12);
            }, i * 40);
        });
    }

    prediction() {
        // Soft wooden pop
        this.playTone(440, 'sine', 0.04, 0.06, 0.04);
    }

    success() {
        // Rewarding harmonious chime
        if (this.muted) return;
        this.init();
        if (!this.ctx) return;

        const chord = [587.33, 739.99, 880.00, 1174.66]; // D major
        chord.forEach((freq, idx) => {
            setTimeout(() => {
                this.playTone(freq, 'sine', 0.35, 0.12, 0.25);
            }, idx * 60);
        });
    }

    fail() {
        // Whimsical curious tone ("You got me!")
        if (this.muted) return;
        this.init();
        if (!this.ctx) return;

        [440, 392, 349.23].forEach((freq, idx) => {
            setTimeout(() => {
                this.playTone(freq, 'triangle', 0.22, 0.09, 0.18);
            }, idx * 80);
        });
    }

    clear() {
        // Quick subtle brush sound
        this.playTone(220, 'sine', 0.06, 0.08, 0.05);
    }
}

export const sound = new SoundEffects();
