// Public AR viewer — runs entirely in the doctor's browser.
// Self-hosted MindAR (image tracking) + three.js with Gemini-powered AI detailing.
import { MindARThree } from "mind-ar/dist/mindar-image-three.prod.js";
import {
    AmbientLight,
    BoxGeometry,
    CanvasTexture,
    DirectionalLight,
    Group,
    Mesh,
    MeshBasicMaterial,
    MeshStandardMaterial,
    PlaneGeometry,
    VideoTexture,
} from "three";

const root = document.getElementById("ar-root");
const startScreen = document.getElementById("ar-start");
const startButton = document.getElementById("ar-start-button");
const statusEl = document.getElementById("ar-status");
const errorScreen = document.getElementById("ar-error");
const errorText = document.getElementById("ar-error-text");

// Overlay UI elements
const greetingBadgeEl = document.getElementById("ar-doctor-greeting");
const greetingTitleEl = document.getElementById("ar-greeting-title");
const greetingSubEl = document.getElementById("ar-greeting-sub");
const productToastEl = document.getElementById("ar-product-toast");
const productNameEl = document.getElementById("ar-product-name");
const productCompEl = document.getElementById("ar-product-comp");
const hint3dEl = document.getElementById("ar-3d-hint");

const config = {
    mindSrc: root.dataset.mind,
    videoSrc: root.dataset.video,
    loop: root.dataset.playMode !== "once",
    markerAspect: parseFloat(root.dataset.markerAspect) || 1,
    recognizeUrl: root.dataset.recognizeUrl,
    csrfToken: root.dataset.csrf,
};

const PRODUCTS = [
    {
        name: "GASTRONIT-DSR",
        composition: "Pantoprazole 40 mg + Domperidone 30 mg",
        speech: "Gastronit DSR provides dual power relief for acid reflux, GERD, and associated nausea.",
        color: "#002b66",
    },
    {
        name: "RABONIT-D",
        composition: "Rabeprazole 20 mg + Domperidone 30 mg",
        speech: "Rabonit D delivers rapid acid control with prokinetic motility support.",
        color: "#d97706",
    },
    {
        name: "ACEVONIT-P",
        composition: "Aceclofenac 100 mg + Paracetamol 325 mg",
        speech: "Acevonit P provides trusted, fast pain relief in musculoskeletal disorders and osteoarthritis.",
        color: "#581c87",
    },
    {
        name: "ACEVONIT-SP",
        composition: "Aceclofenac 100 mg + Paracetamol 325 mg + Serratiopeptidase 15 mg",
        speech: "Acevonit SP brings triple action anti-inflammatory and enzymatic power to rapidly resolve edema.",
        color: "#854d0e",
    },
    {
        name: "LAUR-M",
        composition: "Montelukast 10 mg + Levocetirizine 5 mg",
        speech: "Laur M ensures all day symptom relief from allergic rhinitis and concurrent airway constriction.",
        color: "#0284c7",
    },
    {
        name: "SENSORIX-D3",
        composition: "Methylcobalamin 1.5mg + Alpha Lipoic Acid + Vitamin D3 1000 IU",
        speech: "Sensorix D3 supports nerve repair, neuropathic pain resolution, and metabolic neuroprotection.",
        color: "#ca8a04",
    },
    {
        name: "EXLOFEN-AQ",
        composition: "Diclofenac 75 mg Aqueous",
        speech: "Exlofen AQ gives painless, high bioavailability intramuscular relief for acute pain crises.",
        color: "#ea580c",
    },
];

function fail(message) {
    if (errorText) errorText.textContent = message;
    startScreen?.classList.add("hidden");
    errorScreen?.classList.remove("hidden");
}

if (!window.isSecureContext) {
    fail("AR needs a secure (HTTPS) connection. Open it over HTTPS.");
} else if (!navigator.mediaDevices?.getUserMedia) {
    fail("Your browser can’t open the camera here. Please open this page in Chrome or Safari.");
}

// Optional video plane (if creative has video attached)
const hasVideo = Boolean(config.videoSrc);
const video = document.createElement("video");
if (hasVideo) {
    video.src = config.videoSrc;
    video.loop = config.loop;
    video.playsInline = true;
    video.setAttribute("playsinline", "");
    video.setAttribute("webkit-playsinline", "");
    video.crossOrigin = "anonymous";
    video.preload = "auto";
}

let mindarThree = null;
let started = false;
let isTargetVisible = false;
let hasRecognized = false;
let isSpeakingSequence = false;
let productBoxMesh = null;

// Speech synthesis helper
function speak(text) {
    return new Promise((resolve) => {
        if (!("speechSynthesis" in window)) {
            resolve();
            return;
        }

        window.speechSynthesis.cancel();
        const utter = new SpeechSynthesisUtterance(text);
        utter.rate = 0.95;
        utter.pitch = 1.0;

        utter.onend = () => resolve();
        utter.onerror = () => resolve();

        window.speechSynthesis.speak(utter);
    });
}

// Create a realistic 3D medicine pack with canvas textures
function create3DProductBox() {
    const group = new Group();

    // Box dimensions: width 0.55, height 0.35, depth 0.14
    const geometry = new BoxGeometry(0.55, 0.35, 0.14);

    function createFaceCanvas(title, sub, bandColor = "#003b6d") {
        const canvas = document.createElement("canvas");
        canvas.width = 512;
        canvas.height = 320;
        const ctx = canvas.getContext("2d");

        // White background
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, 512, 320);

        // Header band
        ctx.fillStyle = bandColor;
        ctx.fillRect(0, 0, 512, 75);

        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 26px sans-serif";
        ctx.fillText("EXPONIT LABS", 30, 48);

        // Product title
        ctx.fillStyle = bandColor;
        ctx.font = "bold 42px sans-serif";
        ctx.fillText(title, 30, 150);

        // Subtext / composition
        ctx.fillStyle = "#475569";
        ctx.font = "20px sans-serif";
        ctx.fillText(sub, 30, 195);

        // Pharmaceutical strip graphic
        ctx.fillStyle = "#e2e8f0";
        ctx.fillRect(30, 230, 452, 30);
        ctx.fillStyle = "#94a3b8";
        ctx.font = "14px sans-serif";
        ctx.fillText("10 x 10 Strip Pack • Rx Only", 40, 250);

        // Bottom accent
        ctx.fillStyle = "#00a896";
        ctx.fillRect(0, 305, 512, 15);

        return new CanvasTexture(canvas);
    }

    const frontTex = createFaceCanvas("GASTRONIT-DSR", "Pantoprazole + Domperidone", "#002b66");
    const backTex = createFaceCanvas("EXPONIT LABS", "Healthcare Innovations", "#002b66");
    const sideTex = createFaceCanvas("10 x 10 Capsules", "Store below 25°C", "#002b66");

    const materials = [
        new MeshStandardMaterial({ map: sideTex, roughness: 0.25 }), // right
        new MeshStandardMaterial({ map: sideTex, roughness: 0.25 }), // left
        new MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.2 }), // top
        new MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.2 }), // bottom
        new MeshStandardMaterial({ map: frontTex, roughness: 0.25 }), // front
        new MeshStandardMaterial({ map: backTex, roughness: 0.25 }), // back
    ];

    const mesh = new Mesh(geometry, materials);
    mesh.position.set(0, -0.05, 0.16); // Floats 16cm in front of card
    group.add(mesh);

    return { group, mesh, frontTex };
}

// Touch and drag rotation for 3D Product element
let isDragging = false;
let previousTouch = { x: 0, y: 0 };

window.addEventListener("touchstart", (e) => {
    if (e.touches.length === 1) {
        isDragging = true;
        previousTouch = { x: e.touches[0].clientX, y: e.touches[0].clientY };
    }
});

window.addEventListener("touchmove", (e) => {
    if (!isDragging || !productBoxMesh) return;
    const deltaX = e.touches[0].clientX - previousTouch.x;
    const deltaY = e.touches[0].clientY - previousTouch.y;

    productBoxMesh.rotation.y += deltaX * 0.015;
    productBoxMesh.rotation.x += deltaY * 0.015;

    previousTouch = { x: e.touches[0].clientX, y: e.touches[0].clientY };
});

window.addEventListener("touchend", () => {
    isDragging = false;
});

// Mouse drag fallback for desktop testing
window.addEventListener("mousedown", (e) => {
    isDragging = true;
    previousTouch = { x: e.clientX, y: e.clientY };
});

window.addEventListener("mousemove", (e) => {
    if (!isDragging || !productBoxMesh) return;
    const deltaX = e.clientX - previousTouch.x;
    const deltaY = e.clientY - previousTouch.y;

    productBoxMesh.rotation.y += deltaX * 0.015;
    productBoxMesh.rotation.x += deltaY * 0.015;

    previousTouch = { x: e.clientX, y: e.clientY };
});

window.addEventListener("mouseup", () => {
    isDragging = false;
});

// Capture camera frame and recognize doctor name via backend Gemini AI
async function captureAndRecognizeDoctor() {
    if (!config.recognizeUrl) return null;

    try {
        const videoEl = root.querySelector("video");
        if (!videoEl || !videoEl.videoWidth) return null;

        const canvas = document.createElement("canvas");
        canvas.width = videoEl.videoWidth;
        canvas.height = videoEl.videoHeight;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

        // Convert to lightweight JPEG data url
        const dataUrl = canvas.toDataURL("image/jpeg", 0.8);

        const response = await fetch(config.recognizeUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": config.csrfToken || "",
                Accept: "application/json",
            },
            body: JSON.stringify({ image: dataUrl }),
        });

        if (!response.ok) return null;

        const result = await response.json();
        return result.ok && result.name ? result.name : null;
    } catch (err) {
        console.warn("Recognition error:", err);
        return null;
    }
}

// Master Audio Sequence: Welcome greeting + product detail walkthrough
async function runAudioSequence() {
    if (isSpeakingSequence) return;
    isSpeakingSequence = true;

    // 1. Recognize doctor name in background
    greetingTitleEl.textContent = "Welcome Doctor";
    greetingSubEl.textContent = "Scanning doctor name…";
    greetingBadgeEl?.classList.remove("hidden");

    const doctorName = await captureAndRecognizeDoctor();

    // 2. Determine greeting by time of day
    const hour = new Date().getHours();
    const timeGreeting = hour < 12 ? "Good morning" : hour < 17 ? "Good afternoon" : "Good evening";
    const doctorTitle = doctorName ? `Dr. ${doctorName}` : "Doctor";

    greetingTitleEl.textContent = `${timeGreeting} ${doctorTitle}`;
    greetingSubEl.textContent = "Exponit Labs Product Detailing";
    hint3dEl?.classList.remove("hidden");

    // 3. Welcome Voice
    const welcomeSpeech = `${timeGreeting} ${doctorTitle}. Welcome to Exponit Labs. Here is our product range.`;
    await speak(welcomeSpeech);

    // 4. Products sequence
    for (const product of PRODUCTS) {
        if (!isTargetVisible) break;

        // Update floating product toast
        productToastEl?.classList.remove("hidden");
        if (productNameEl) productNameEl.textContent = product.name;
        if (productCompEl) productCompEl.textContent = product.composition;

        await speak(`${product.name}. ${product.speech}`);
        await new Promise((r) => setTimeout(r, 400));
    }

    isSpeakingSequence = false;
}

async function start() {
    if (started) return;
    started = true;
    startScreen?.classList.add("hidden");

    if (hasVideo) {
        try {
            await video.play();
            video.pause();
            video.currentTime = 0;
        } catch (e) {
            // Ignore — resumes on target detection
        }
    }

    try {
        mindarThree = new MindARThree({
            container: root,
            imageTargetSrc: config.mindSrc,
            uiScanning: "no",
            uiLoading: "no",
            uiError: "no",
            filterMinCF: 0.0001,
            filterBeta: 1,
        });

        const { renderer, scene, camera } = mindarThree;
        const anchor = mindarThree.addAnchor(0);

        // Lighting for 3D packaging
        const ambientLight = new AmbientLight(0xffffff, 0.9);
        anchor.group.add(ambientLight);

        const dirLight = new DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(1, 2, 3);
        anchor.group.add(dirLight);

        // Optional flat video plane
        if (hasVideo) {
            const texture = new VideoTexture(video);
            const geometry = new PlaneGeometry(1, config.markerAspect);
            const material = new MeshBasicMaterial({
                map: texture,
                transparent: true,
            });
            const plane = new Mesh(geometry, material);
            anchor.group.add(plane);
        }

        // 3D Rotatable Product Box
        const { group: boxGroup, mesh: boxMesh } = create3DProductBox();
        productBoxMesh = boxMesh;
        anchor.group.add(boxGroup);

        anchor.onTargetFound = () => {
            isTargetVisible = true;
            statusEl?.classList.add("hidden");
            if (hasVideo) video.play().catch(() => {});

            if (!hasRecognized) {
                hasRecognized = true;
                runAudioSequence();
            }
        };

        anchor.onTargetLost = () => {
            isTargetVisible = false;
            statusEl?.classList.remove("hidden");
            if (hasVideo) video.pause();
            window.speechSynthesis?.pause();
        };

        await mindarThree.start();

        renderer.setAnimationLoop(() => {
            // Idle auto-rotation when not dragged
            if (!isDragging && productBoxMesh) {
                productBoxMesh.rotation.y += 0.006;
            }
            renderer.render(scene, camera);
        });

        statusEl?.classList.remove("hidden");

        setTimeout(() => window.dispatchEvent(new Event("resize")), 300);
    } catch (e) {
        fail("Couldn’t start the camera. Please allow camera access and reload.");
    }
}

startButton?.addEventListener("click", start);

