/**
 * Exponit Draw — Main Application Bootloader
 */

import { sound } from './audio.js';
import { DrawingCanvas } from './canvas.js';
import { createClassifier } from './classifier.js';
import { DebugPanel } from './debug.js';
import { GameEngine, GameState } from './game.js';
import { initPWA } from './pwa.js';

class UIController {
    constructor() {
        // Screens
        this.landingScreen = document.getElementById('screen-landing');
        this.countdownScreen = document.getElementById('screen-countdown');
        this.drawingScreen = document.getElementById('screen-drawing');
        this.resultScreen = document.getElementById('screen-result');

        // Dynamic Elements
        this.promptText = document.getElementById('drawing-target-prompt');
        this.timerText = document.getElementById('timer-display');
        this.countdownNumber = document.getElementById('countdown-number');
        this.countdownPrompt = document.getElementById('countdown-target-prompt');

        // Live prediction bar
        this.predCopy = document.getElementById('pred-copy');
        this.predEmoji = document.getElementById('pred-emoji');
        this.predLabel = document.getElementById('pred-label');
        this.predConfidence = document.getElementById('pred-confidence');
        this.predMeter = document.getElementById('pred-meter-bar');

        // Result screen
        this.resultStatus = document.getElementById('result-status-title');
        this.resultSubtitle = document.getElementById('result-subtitle');
        this.resultEmoji = document.getElementById('result-emoji');
        this.resultGuess = document.getElementById('result-guess-label');
        this.resultMeta = document.getElementById('result-meta-info');

        // Audio buttons
        this.muteButtons = document.querySelectorAll('.btn-mute-toggle');

        // Toast
        this.toast = document.getElementById('ui-toast');
        this.toastTimeout = null;

        this.updateMuteButtons();
    }

    renderState(state, data = {}) {
        [this.landingScreen, this.countdownScreen, this.drawingScreen, this.resultScreen].forEach(s => {
            if (s) s.classList.add('hidden');
        });

        switch (state) {
            case GameState.READY:
                this.landingScreen?.classList.remove('hidden');
                break;

            case GameState.COUNTDOWN:
                if (this.countdownPrompt && data.challenge) {
                    this.countdownPrompt.textContent = data.challenge.label;
                }
                this.countdownScreen?.classList.remove('hidden');
                break;

            case GameState.DRAWING:
                if (this.promptText && data.challenge) {
                    this.promptText.textContent = data.challenge.label;
                }
                this.drawingScreen?.classList.remove('hidden');
                if (this.onDrawingReady) {
                    this.onDrawingReady();
                }
                this.updateLivePrediction({
                    copy: "I'M WATCHING...",
                    emoji: '✏️',
                    guess: '',
                    confidence: 0
                });
                break;

            case GameState.RESULT:
                this.renderResultScreen(data);
                this.resultScreen?.classList.remove('hidden');
                break;
        }
    }

    updateCountdown(val) {
        if (this.countdownNumber) {
            this.countdownNumber.textContent = val;
            this.countdownNumber.classList.remove('pulse-num');
            void this.countdownNumber.offsetWidth; // trigger reflow
            this.countdownNumber.classList.add('pulse-num');
        }
    }

    updateTimer(seconds) {
        if (this.timerText) {
            const formatted = `00:${String(seconds).padStart(2, '0')}`;
            this.timerText.textContent = formatted;
            if (seconds <= 5) {
                this.timerText.classList.add('timer-urgent');
            } else {
                this.timerText.classList.remove('timer-urgent');
            }
        }
    }

    updateLivePrediction({ copy, emoji, guess, confidence }) {
        if (this.predCopy) this.predCopy.textContent = copy || "I'M WATCHING...";
        if (this.predEmoji) this.predEmoji.textContent = emoji || '✏️';
        if (this.predLabel) this.predLabel.textContent = guess ? `${guess}` : '';
        if (this.predConfidence) {
            this.predConfidence.textContent = confidence > 0 ? `${confidence}%` : '';
        }
        if (this.predMeter) {
            this.predMeter.style.width = `${confidence || 0}%`;
        }
    }

    renderResultScreen({ challenge, lastPrediction }) {
        // Case 1: Empty canvas / time ran out without drawing
        if (!lastPrediction || lastPrediction.status === 'empty' || !lastPrediction.guess) {
            this.resultStatus.textContent = "TIME'S UP!";
            this.resultStatus.className = "result-title text-curious";
            this.resultSubtitle.textContent = "You didn't draw anything! Tap below to try.";
            if (this.resultEmoji) this.resultEmoji.textContent = '⏱️';
            if (this.resultGuess) this.resultGuess.textContent = challenge?.label || '';
            if (this.resultMeta) {
                this.resultMeta.textContent = `Challenge was: ${challenge?.label || ''}`;
            }
            return;
        }

        const isCorrect = lastPrediction.isCorrect;
        const guess = lastPrediction.guess;
        const emoji = lastPrediction.emoji || challenge.emoji;
        const confidence = lastPrediction.confidence || 0;

        if (isCorrect) {
            this.resultStatus.textContent = "I GOT IT!";
            this.resultStatus.className = "result-title text-success";
            this.resultSubtitle.textContent = `I recognized your ${challenge.label}!`;
        } else {
            this.resultStatus.textContent = "YOU GOT ME.";
            this.resultStatus.className = "result-title text-curious";
            this.resultSubtitle.textContent = `You drew ${challenge.label}, but I thought it was a ${guess}.`;
        }

        if (this.resultEmoji) this.resultEmoji.textContent = emoji;
        if (this.resultGuess) this.resultGuess.textContent = guess;
        if (this.resultMeta) {
            this.resultMeta.textContent = `Target: ${challenge.label} • Confidence: ${confidence}%`;
        }
    }

    updateMuteButtons() {
        const isMuted = sound.isMuted();
        this.muteButtons.forEach(btn => {
            btn.innerHTML = isMuted ? '🔇' : '🔊';
            btn.setAttribute('aria-label', isMuted ? 'Unmute sound' : 'Mute sound');
        });
    }

    showToast(message) {
        if (!this.toast) return;
        this.toast.textContent = message;
        this.toast.classList.remove('hidden');
        clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => {
            this.toast.classList.add('hidden');
        }, 2200);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const canvasElement = document.getElementById('drawing-canvas');
    const debugElement = document.getElementById('debug-overlay');

    const debug = new DebugPanel(debugElement);
    const ui = new UIController();
    const classifier = await createClassifier();

    debug.update({
        classifier: classifier.type === 'tfjs' ? `TFJS (${classifier.backend})` : 'MockClassifier'
    });

    const canvas = new DrawingCanvas(canvasElement, (stats) => {
        debug.update({
            strokes: stats.strokeCount,
            points: stats.pointCount,
            pointerType: canvas.lastPointerType
        });
    });

    const game = new GameEngine({ canvas, classifier, debug, ui });
    await game.init();

    ui.onDrawingReady = () => {
        requestAnimationFrame(() => {
            canvas.resize();
        });
    };

    // Initialize dedicated PWA service worker with /lab/draw scope
    initPWA((status) => {
        debug.update(status);
    });

    // Button Bindings
    document.getElementById('btn-start-game')?.addEventListener('click', () => {
        game.startRound(false);
    });

    document.getElementById('btn-clear-canvas')?.addEventListener('click', () => {
        game.clearCanvas();
    });

    document.getElementById('btn-done-drawing')?.addEventListener('click', () => {
        game.finishRound(false);
    });

    document.getElementById('btn-replay-same')?.addEventListener('click', () => {
        game.replaySame();
    });

    document.getElementById('btn-replay-new')?.addEventListener('click', () => {
        game.replayNew();
    });

    // Mute Toggles
    ui.muteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            sound.toggleMute();
            ui.updateMuteButtons();
        });
    });

    // Triple-tap header to toggle debug mode
    let tapCount = 0;
    let tapTimer = null;
    const headerTitle = document.getElementById('header-app-brand');
    if (headerTitle) {
        headerTitle.addEventListener('click', () => {
            tapCount++;
            clearTimeout(tapTimer);
            tapTimer = setTimeout(() => { tapCount = 0; }, 600);
            if (tapCount >= 3) {
                tapCount = 0;
                debug.toggle();
            }
        });
    }
});
