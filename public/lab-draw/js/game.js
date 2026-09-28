/**
 * Exponit Draw — Core Game Engine & State Machine
 * 
 * Manages:
 * - Game lifecycle (BOOT -> READY -> COUNTDOWN -> DRAWING -> RESULT)
 * - 20s countdown timer
 * - Throttled live inference loop
 * - Challenge cycling & replay loops
 * - Sound triggering & debug telemetry
 */

import { CATEGORIES } from './classifier.js';
import { sound } from './audio.js';

export const GameState = {
    BOOT: 'BOOT',
    READY: 'READY',
    COUNTDOWN: 'COUNTDOWN',
    DRAWING: 'DRAWING',
    RESULT: 'RESULT'
};

export class GameEngine {
    constructor({ canvas, classifier, debug, ui }) {
        this.canvas = canvas;
        this.classifier = classifier;
        this.debug = debug;
        this.ui = ui;

        this.state = GameState.BOOT;
        this.currentChallenge = null;
        this.previousChallenge = null;

        // Timer
        this.roundDurationSec = 20;
        this.timeRemainingSec = 20;
        this.timerInterval = null;

        // Inference loop
        this.inferenceInterval = null;
        this.isInferenceRunning = false;
        this.lastPrediction = null;

        // Round analytics tracking
        this.roundStartTime = 0;
        this.roundDurationMs = 0;
    }

    async init() {
        this.setState(GameState.BOOT);
        await this.classifier.initialize();
        this.selectNextChallenge();
        this.setState(GameState.READY);
    }

    setState(newState) {
        this.state = newState;
        this.debug.update({ state: newState });
        this.ui.renderState(newState, {
            challenge: this.currentChallenge,
            timeRemaining: this.timeRemainingSec,
            lastPrediction: this.lastPrediction
        });
    }

    selectNextChallenge() {
        const pool = CATEGORIES.filter(c => !this.previousChallenge || c.id !== this.previousChallenge.id);
        const selected = pool[Math.floor(Math.random() * pool.length)];
        this.previousChallenge = this.currentChallenge;
        this.currentChallenge = selected;
        this.debug.update({ challenge: selected.id });
        return selected;
    }

    startRound(reuseSameChallenge = false) {
        sound.init(); // unlock audio context

        if (!reuseSameChallenge) {
            this.selectNextChallenge();
        }

        this.canvas.clear();
        this.classifier.reset();
        this.lastPrediction = null;
        this.timeRemainingSec = this.roundDurationSec;

        this.setState(GameState.COUNTDOWN);
        this.startCountdown();
    }

    startCountdown() {
        let count = 3;
        this.ui.updateCountdown(count);
        sound.tick();

        const cdInterval = setInterval(() => {
            count--;
            if (count > 0) {
                this.ui.updateCountdown(count);
                sound.tick();
            } else if (count === 0) {
                this.ui.updateCountdown('GO!');
                sound.countdownGo();
            } else {
                clearInterval(cdInterval);
                this.beginDrawingPhase();
            }
        }, 900);
    }

    beginDrawingPhase() {
        this.roundStartTime = Date.now();
        this.setState(GameState.DRAWING);
        this.canvas.resize();
        this.startTimer();
        this.startInferenceLoop();
    }

    startTimer() {
        clearInterval(this.timerInterval);
        this.ui.updateTimer(this.timeRemainingSec);

        this.timerInterval = setInterval(() => {
            this.timeRemainingSec--;
            this.ui.updateTimer(this.timeRemainingSec);

            if (this.timeRemainingSec <= 5 && this.timeRemainingSec > 0) {
                sound.tick();
            }

            if (this.timeRemainingSec <= 0) {
                clearInterval(this.timerInterval);
                this.finishRound(true);
            }
        }, 1000);
    }

    startInferenceLoop() {
        clearInterval(this.inferenceInterval);

        // Run live inference throttled at 350ms
        this.inferenceInterval = setInterval(async () => {
            if (this.state !== GameState.DRAWING || this.isInferenceRunning) return;
            if (this.canvas.isEmpty()) {
                this.ui.updateLivePrediction({
                    status: 'waiting',
                    copy: "I'M WATCHING...",
                    emoji: '✏️',
                    guess: '',
                    confidence: 0
                });
                return;
            }

            this.isInferenceRunning = true;
            try {
                const prediction = await this.classifier.predict({
                    canvas: this.canvas,
                    strokes: this.canvas.strokes,
                    timeRemaining: this.timeRemainingSec,
                    strokeCount: this.canvas.strokes.length,
                    pointCount: this.canvas.pointCount,
                    targetChallenge: this.currentChallenge.id,
                    isFinal: false
                });

                this.lastPrediction = prediction;
                this.ui.updateLivePrediction(prediction);

                // Subtle audio feedback if confidence crosses threshold
                if (prediction.confidence > 60 && (!this.lastHighConfPing || Date.now() - this.lastHighConfPing > 3000)) {
                    sound.prediction();
                    this.lastHighConfPing = Date.now();
                }

                this.debug.update({
                    inferenceCount: this.classifier.inferenceCount,
                    lastLatency: this.classifier.lastLatencyMs,
                    strokes: this.canvas.strokes.length,
                    points: this.canvas.pointCount,
                    pointerType: this.canvas.lastPointerType
                });
            } finally {
                this.isInferenceRunning = false;
            }
        }, 350);
    }

    clearCanvas() {
        this.canvas.clear();
        this.classifier.reset();
        sound.clear();
        this.lastPrediction = null;
        this.ui.updateLivePrediction({
            status: 'waiting',
            copy: "CANVAS CLEARED. GO ON!",
            emoji: '✏️',
            guess: '',
            confidence: 0
        });
        this.debug.update({ strokes: 0, points: 0 });
    }

    async finishRound(isTimeOut = false) {
        clearInterval(this.timerInterval);
        clearInterval(this.inferenceInterval);

        const isEmpty = this.canvas.isEmpty();

        if (isEmpty) {
            if (!isTimeOut) {
                this.ui.showToast('Draw something first!');
                this.startTimer();
                this.startInferenceLoop();
                return;
            }

            // Time expired without drawing
            this.roundDurationMs = Date.now() - this.roundStartTime;
            this.lastPrediction = {
                status: 'empty',
                copy: "TIME'S UP!",
                guess: null,
                emoji: '⏱️',
                confidence: 0,
                isCorrect: false,
                rawPredictions: []
            };
            this.setState(GameState.RESULT);
            sound.fail();
            return;
        }

        this.roundDurationMs = Date.now() - this.roundStartTime;

        // Final inference pass on actual drawing
        const finalPrediction = await this.classifier.predict({
            canvas: this.canvas,
            strokes: this.canvas.strokes,
            timeRemaining: 0,
            strokeCount: this.canvas.strokes.length,
            pointCount: this.canvas.pointCount,
            targetChallenge: this.currentChallenge.id,
            isFinal: true
        });

        this.lastPrediction = finalPrediction;
        this.setState(GameState.RESULT);

        if (finalPrediction.isCorrect) {
            sound.success();
        } else {
            sound.fail();
        }
    }

    replaySame() {
        this.startRound(true);
    }

    replayNew() {
        this.startRound(false);
    }
}
