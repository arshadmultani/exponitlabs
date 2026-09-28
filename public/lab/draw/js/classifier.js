/**
 * Classifier Abstraction & Mock Implementation for Exponit Draw
 * 
 * Game Engine interacts only with the Classifier interface:
 *   - initialize()
 *   - predict({ canvas, strokes, timeRemaining, strokeCount, pointCount, targetChallenge })
 *   - reset()
 *   - dispose()
 */

export class Classifier {
    async initialize() {
        throw new Error('initialize() must be implemented');
    }

    /**
     * @param {Object} input 
     * @returns {Promise<{ guess: string, confidence: number, isCorrect: boolean, status: string, rawPredictions: Array }>}
     */
    async predict(input) {
        throw new Error('predict() must be implemented');
    }

    reset() {}

    dispose() {}
}

export const CATEGORIES = [
    { id: 'bicycle', label: 'BICYCLE', emoji: '🚲' },
    { id: 'car', label: 'CAR', emoji: '🚗' },
    { id: 'airplane', label: 'AIRPLANE', emoji: '✈️' },
    { id: 'rocket', label: 'ROCKET', emoji: '🚀' },
    { id: 'house', label: 'HOUSE', emoji: '🏠' },
    { id: 'tree', label: 'TREE', emoji: '🌳' },
    { id: 'flower', label: 'FLOWER', emoji: '🌸' },
    { id: 'sun', label: 'SUN', emoji: '☀️' },
    { id: 'star', label: 'STAR', emoji: '⭐' },
    { id: 'heart', label: 'HEART', emoji: '❤️' },
    { id: 'apple', label: 'APPLE', emoji: '🍎' },
    { id: 'pizza', label: 'PIZZA', emoji: '🍕' },
    { id: 'coffee cup', label: 'COFFEE CUP', emoji: '☕' },
    { id: 'dog', label: 'DOG', emoji: '🐶' },
    { id: 'cat', label: 'CAT', emoji: '🐱' },
    { id: 'guitar', label: 'GUITAR', emoji: '🎸' },
    { id: 'camera', label: 'CAMERA', emoji: '📷' },
    { id: 'watch', label: 'WATCH', emoji: '⌚' },
    { id: 'phone', label: 'PHONE', emoji: '📱' },
    { id: 'umbrella', label: 'UMBRELLA', emoji: '☂️' }
];

export class MockClassifier extends Classifier {
    constructor() {
        super();
        this.isReady = false;
        this.inferenceCount = 0;
        this.lastLatencyMs = 0;
        this.type = 'mock';
        this.lastPrediction = null;
        this.currentConfidence = 0.1;
    }

    async initialize() {
        // Fast mock initialization
        this.isReady = true;
        this.inferenceCount = 0;
        return true;
    }

    reset() {
        this.currentConfidence = 0.1;
        this.lastPrediction = null;
    }

    dispose() {
        this.isReady = false;
    }

    /**
     * Simulates intelligent progressive inference
     */
    async predict({ canvas, strokes, timeRemaining, strokeCount, pointCount, targetChallenge, isFinal = false }) {
        const start = performance.now();
        this.inferenceCount++;

        // Simulate tiny realistic inference latency (12-28ms)
        await new Promise(resolve => setTimeout(resolve, Math.floor(Math.random() * 16) + 12));

        if (!strokes || strokes.length === 0 || pointCount < 8) {
            this.lastLatencyMs = Math.round(performance.now() - start);
            return {
                status: 'waiting',
                copy: "I'M WATCHING...",
                guess: null,
                emoji: '✏️',
                confidence: 0,
                isCorrect: false,
                rawPredictions: []
            };
        }

        const targetCat = CATEGORIES.find(c => c.id === targetChallenge) || {
            id: targetChallenge,
            label: targetChallenge.toUpperCase(),
            emoji: '✨'
        };

        // Pick alternative plausible guesses
        const distractors = CATEGORIES.filter(c => c.id !== targetCat.id);
        const distractor1 = distractors[Math.floor(Math.random() * distractors.length)];
        const distractor2 = distractors[(Math.floor(Math.random() * distractors.length) + 1) % distractors.length];

        // Progressive confidence based on stroke complexity
        let targetConfidence;
        let guess = targetCat;
        let isCorrect = true;

        if (strokeCount <= 2 && pointCount < 40) {
            // Early stage: guessing vague or distractor
            if (Math.random() > 0.4) {
                guess = distractor1;
                targetConfidence = Math.min(0.42, 0.18 + pointCount * 0.006);
                isCorrect = false;
            } else {
                guess = targetCat;
                targetConfidence = Math.min(0.48, 0.22 + pointCount * 0.007);
            }
        } else if (strokeCount <= 4 && pointCount < 100) {
            // Mid stage: narrowing down
            guess = targetCat;
            targetConfidence = Math.min(0.74, 0.45 + (pointCount - 40) * 0.005);
        } else {
            // Well drawn stage: high confidence
            guess = targetCat;
            targetConfidence = Math.min(0.96, 0.72 + (pointCount - 100) * 0.002);
        }

        // If finalized with very few strokes, deliberate 20% surprise miss ("You got me!")
        if (isFinal && (strokeCount < 2 || pointCount < 30)) {
            guess = distractor1;
            targetConfidence = 0.55;
            isCorrect = false;
        }

        // Smooth confidence to avoid erratic jumps
        this.currentConfidence = Number((this.currentConfidence * 0.4 + targetConfidence * 0.6).toFixed(2));

        // Format conversational copy according to section 65
        let copy = "I THINK IT'S...";
        if (this.currentConfidence < 0.35) {
            copy = "I'M THINKING...";
        } else if (this.currentConfidence > 0.8) {
            copy = "I KNOW THIS ONE!";
        } else if (this.currentConfidence > 0.55) {
            copy = `IS IT A ${guess.label}?`;
        }

        this.lastLatencyMs = Math.round(performance.now() - start);

        const result = {
            status: 'guessing',
            copy,
            guess: guess.label,
            emoji: guess.emoji,
            confidence: Math.round(this.currentConfidence * 100),
            isCorrect,
            rawPredictions: [
                { label: guess.label, confidence: Math.round(this.currentConfidence * 100) },
                { label: distractor1.label, confidence: Math.max(5, Math.round((1 - this.currentConfidence) * 60)) },
                { label: distractor2.label, confidence: Math.max(3, Math.round((1 - this.currentConfidence) * 40)) }
            ]
        };

        this.lastPrediction = result;
        return result;
    }
}
