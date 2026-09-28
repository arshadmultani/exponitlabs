/**
 * TensorFlow.js Classifier & Architecture for Exponit Draw
 * 
 * Features:
 * - Local TensorFlow.js execution (no CDN, zero remote inference)
 * - 28x28 normalized grayscale input tensor [1, 28, 28, 1]
 * - WebGL backend with CPU fallback
 * - Memory leak prevention with tf.tidy()
 * - Exponential prediction smoothing across inference ticks
 * - Synonym matching for QuickDraw 345-class model
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
    { id: 'bicycle', modelClass: 'bicycle', label: 'BICYCLE', emoji: '🚲', synonyms: ['bicycle'] },
    { id: 'car', modelClass: 'car', label: 'CAR', emoji: '🚗', synonyms: ['car', 'pickup_truck', 'police_car', 'truck', 'van'] },
    { id: 'airplane', modelClass: 'airplane', label: 'AIRPLANE', emoji: '✈️', synonyms: ['airplane', 'aircraft_carrier'] },
    { id: 'house', modelClass: 'house', label: 'HOUSE', emoji: '🏠', synonyms: ['house', 'barn', 'church', 'castle'] },
    { id: 'tree', modelClass: 'tree', label: 'TREE', emoji: '🌳', synonyms: ['tree', 'palm_tree', 'bush'] },
    { id: 'flower', modelClass: 'flower', label: 'FLOWER', emoji: '🌸', synonyms: ['flower', 'house_plant'] },
    { id: 'sun', modelClass: 'sun', label: 'SUN', emoji: '☀️', synonyms: ['sun'] },
    { id: 'star', modelClass: 'star', label: 'STAR', emoji: '⭐', synonyms: ['star'] },
    { id: 'apple', modelClass: 'apple', label: 'APPLE', emoji: '🍎', synonyms: ['apple'] },
    { id: 'pizza', modelClass: 'pizza', label: 'PIZZA', emoji: '🍕', synonyms: ['pizza'] },
    { id: 'coffee cup', modelClass: 'coffee_cup', label: 'COFFEE CUP', emoji: '☕', synonyms: ['coffee_cup', 'cup', 'mug', 'teapot'] },
    { id: 'dog', modelClass: 'dog', label: 'DOG', emoji: '🐶', synonyms: ['dog'] },
    { id: 'cat', modelClass: 'cat', label: 'CAT', emoji: '🐱', synonyms: ['cat'] },
    { id: 'guitar', modelClass: 'guitar', label: 'GUITAR', emoji: '🎸', synonyms: ['guitar', 'violin', 'cello', 'harp'] },
    { id: 'camera', modelClass: 'camera', label: 'CAMERA', emoji: '📷', synonyms: ['camera'] },
    { id: 'watch', modelClass: 'wristwatch', label: 'WATCH', emoji: '⌚', synonyms: ['wristwatch', 'clock', 'alarm_clock'] },
    { id: 'phone', modelClass: 'cell_phone', label: 'PHONE', emoji: '📱', synonyms: ['cell_phone', 'telephone'] },
    { id: 'umbrella', modelClass: 'umbrella', label: 'UMBRELLA', emoji: '☂️', synonyms: ['umbrella', 'parachute'] },
    { id: 'circle', modelClass: 'circle', label: 'CIRCLE', emoji: '⭕', synonyms: ['circle', 'donut', 'wheel', 'hockey_puck'] },
    { id: 'cloud', modelClass: 'cloud', label: 'CLOUD', emoji: '☁️', synonyms: ['cloud', 'rain'] },
    { id: 'fish', modelClass: 'fish', label: 'FISH', emoji: '🐟', synonyms: ['fish', 'shark'] },
    { id: 'smiley face', modelClass: 'smiley_face', label: 'SMILEY FACE', emoji: '😊', synonyms: ['smiley_face', 'face'] },
    { id: 'shoe', modelClass: 'shoe', label: 'SHOE', emoji: '👟', synonyms: ['shoe', 'rollerskates', 'flip_flops'] },
    { id: 'hat', modelClass: 'hat', label: 'HAT', emoji: '🎩', synonyms: ['hat', 'crown', 'helmet'] }
];

const EMOJI_MAP = {
    bicycle: '🚲', car: '🚗', airplane: '✈️', house: '🏠', tree: '🌳',
    flower: '🌸', sun: '☀️', star: '⭐', apple: '🍎', pizza: '🍕',
    coffee_cup: '☕', dog: '🐶', cat: '🐱', guitar: '🎸', camera: '📷',
    wristwatch: '⌚', cell_phone: '📱', umbrella: '☂️', circle: '⭕',
    cloud: '☁️', fish: '🐟', smiley_face: '😊', shoe: '👟', hat: '🎩',
    banana: '🍌', strawberry: '🍓', mushroom: '🍄', ice_cream: '🍦',
    donut: '🍩', cookie: '🍪', cup: '🥤', clock: '⏰', scissors: '✂️',
    pencil: '✏️', book: '📖', candle: '🕯️', key: '🔑', glasses: '👓',
    headphones: '🎧', moon: '🌙', rainbow: '🌈', lightning: '⚡',
    spider: '🕷️', butterfly: '🦋', bird: '🐦', duck: '🦆'
};

function getEmoji(className) {
    if (EMOJI_MAP[className]) return EMOJI_MAP[className];
    const cat = CATEGORIES.find(c => c.modelClass === className || c.synonyms.includes(className));
    return cat ? cat.emoji : '🎨';
}

function formatLabel(className) {
    return className.replace(/_/g, ' ').toUpperCase();
}

/**
 * Production Local TensorFlow.js Classifier
 */
export class TensorFlowJSClassifier extends Classifier {
    constructor() {
        super();
        this.model = null;
        this.classes = [];
        this.isReady = false;
        this.backend = 'unknown';
        this.inferenceCount = 0;
        this.lastLatencyMs = 0;
        this.type = 'tfjs';
        this.smoothedProbabilities = null;
    }

    async initialize() {
        if (typeof window === 'undefined' || !window.tf) {
            throw new Error('TensorFlow.js (tf) is not loaded in window context.');
        }

        const tf = window.tf;

        // 1. Choose fastest reliable backend
        try {
            await tf.setBackend('webgl');
            tf.env().set('WEBGL_PACK', true);
            this.backend = 'webgl';
        } catch (e) {
            console.warn('[TFJS] WebGL not available, falling back to CPU:', e);
            await tf.setBackend('cpu');
            this.backend = 'cpu';
        }
        await tf.ready();

        // 2. Fetch class names
        const classRes = await fetch('/lab/draw/model/class_names.txt');
        if (!classRes.ok) {
            throw new Error(`Failed to load class_names.txt: ${classRes.status}`);
        }
        const text = await classRes.text();
        this.classes = text.trim().split(/\r?\n/).map(s => s.trim());

        // 3. Load Layers Model
        this.model = await tf.loadLayersModel('/lab/draw/model/model.json');

        // 4. Warm up model with dummy tensor to avoid initial UI freeze
        tf.tidy(() => {
            const dummy = tf.zeros([1, 28, 28, 1]);
            this.model.predict(dummy);
        });

        this.isReady = true;
        this.inferenceCount = 0;
        return true;
    }

    reset() {
        this.smoothedProbabilities = null;
    }

    dispose() {
        if (this.model) {
            try {
                this.model.dispose();
            } catch (_) {}
            this.model = null;
        }
        this.isReady = false;
    }

    /**
     * Executes local real-time classification
     */
    async predict({ canvas, strokes, timeRemaining, strokeCount, pointCount, targetChallenge, isFinal = false }) {
        if (!this.isReady || !this.model || !strokes || strokes.length === 0 || pointCount < 8) {
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

        const tf = window.tf;
        const start = performance.now();
        this.inferenceCount++;

        // 1. Get 28x28 normalized canvas from DrawingCanvas
        const offscreen = canvas.getPreprocessedCanvas ? canvas.getPreprocessedCanvas(28) : null;
        if (!offscreen) {
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

        // 2. Run inference inside tf.tidy to guarantee zero GPU memory leaks
        const rawProbabilities = tf.tidy(() => {
            const tensor = tf.browser.fromPixels(offscreen, 1)
                .toFloat()
                .div(255.0)
                .expandDims(0); // Shape [1, 28, 28, 1]

            const output = this.model.predict(tensor);
            return output.dataSync(); // Float32Array of length 345
        });

        this.lastLatencyMs = Math.round(performance.now() - start);

        // 3. Temporal Prediction Smoothing (EMA filter: 65% new, 35% past)
        if (!this.smoothedProbabilities || this.smoothedProbabilities.length !== rawProbabilities.length) {
            this.smoothedProbabilities = new Float32Array(rawProbabilities);
        } else {
            for (let i = 0; i < rawProbabilities.length; i++) {
                this.smoothedProbabilities[i] = this.smoothedProbabilities[i] * 0.35 + rawProbabilities[i] * 0.65;
            }
        }

        // 4. Rank top predictions
        const ranked = [];
        for (let i = 0; i < this.classes.length; i++) {
            ranked.push({
                className: this.classes[i],
                prob: this.smoothedProbabilities[i]
            });
        }
        ranked.sort((a, b) => b.prob - a.prob);

        const top1 = ranked[0];
        const top2 = ranked[1] || top1;
        const top3 = ranked[2] || top2;

        const targetCat = CATEGORIES.find(c => c.id === targetChallenge);
        const targetModelClass = targetCat ? targetCat.modelClass : targetChallenge;
        const targetSynonyms = targetCat ? targetCat.synonyms : [targetChallenge];

        // 5. Evaluate correctness (checks if top prediction matches target class or valid synonyms)
        const isTopMatch = targetSynonyms.includes(top1.className);
        // Also check if target is in top 3 with solid probability (> 0.20)
        const inTop3Match = ranked.slice(0, 3).some(r => targetSynonyms.includes(r.className) && r.prob > 0.20);
        const isCorrect = isTopMatch || (isFinal && inTop3Match);

        // When correct in final state, surface target label
        const displayClass = (isCorrect && !isTopMatch) ? targetModelClass : top1.className;
        const displayLabel = formatLabel(displayClass);
        const displayEmoji = getEmoji(displayClass);
        const confidencePercent = Math.min(99, Math.max(12, Math.round(top1.prob * 100)));

        // 6. Conversational copy
        let copy = "I THINK IT'S...";
        if (confidencePercent < 28) {
            copy = "I'M THINKING...";
        } else if (confidencePercent > 80) {
            copy = `I KNOW THIS: ${displayLabel}!`;
        } else if (confidencePercent > 50) {
            copy = `IS IT A ${displayLabel}?`;
        }

        const rawPredictions = [top1, top2, top3].map(p => ({
            label: formatLabel(p.className),
            confidence: Math.round(p.prob * 100)
        }));

        return {
            status: 'guessing',
            copy,
            guess: displayLabel,
            emoji: displayEmoji,
            confidence: confidencePercent,
            isCorrect,
            rawPredictions
        };
    }
}

/**
 * Mock Classifier (Used for Testing & Fallback)
 */
export class MockClassifier extends Classifier {
    constructor() {
        super();
        this.isReady = false;
        this.inferenceCount = 0;
        this.lastLatencyMs = 0;
        this.type = 'mock';
        this.currentConfidence = 0.1;
    }

    async initialize() {
        this.isReady = true;
        this.inferenceCount = 0;
        return true;
    }

    reset() {
        this.currentConfidence = 0.1;
    }

    dispose() {
        this.isReady = false;
    }

    async predict({ strokes, pointCount, targetChallenge, isFinal = false }) {
        const start = performance.now();
        this.inferenceCount++;

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

        const distractors = CATEGORIES.filter(c => c.id !== targetCat.id);
        const distractor1 = distractors[Math.floor(Math.random() * distractors.length)];
        const distractor2 = distractors[(Math.floor(Math.random() * distractors.length) + 1) % distractors.length];

        let targetConfidence;
        let guess = targetCat;
        let isCorrect = true;

        if (pointCount < 40) {
            guess = distractor1;
            targetConfidence = Math.min(0.42, 0.20 + pointCount * 0.005);
            isCorrect = false;
        } else if (pointCount < 100) {
            guess = targetCat;
            targetConfidence = Math.min(0.75, 0.45 + (pointCount - 40) * 0.005);
        } else {
            guess = targetCat;
            targetConfidence = Math.min(0.96, 0.72 + (pointCount - 100) * 0.002);
        }

        if (isFinal && pointCount < 30) {
            guess = distractor1;
            targetConfidence = 0.55;
            isCorrect = false;
        }

        this.currentConfidence = Number((this.currentConfidence * 0.4 + targetConfidence * 0.6).toFixed(2));

        let copy = "I THINK IT'S...";
        if (this.currentConfidence < 0.35) {
            copy = "I'M THINKING...";
        } else if (this.currentConfidence > 0.8) {
            copy = "I KNOW THIS ONE!";
        } else if (this.currentConfidence > 0.55) {
            copy = `IS IT A ${guess.label}?`;
        }

        this.lastLatencyMs = Math.round(performance.now() - start);

        return {
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
    }
}

/**
 * Factory helper: Initializes TensorFlowJSClassifier with fallback to MockClassifier
 */
export async function createClassifier() {
    if (typeof window !== 'undefined' && window.tf) {
        try {
            const tfClassifier = new TensorFlowJSClassifier();
            await tfClassifier.initialize();
            return tfClassifier;
        } catch (err) {
            console.warn('[Classifier] TensorFlowJSClassifier failed to initialize, falling back to MockClassifier:', err);
        }
    }
    const mock = new MockClassifier();
    await mock.initialize();
    return mock;
}
