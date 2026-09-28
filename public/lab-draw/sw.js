/**
 * Dedicated Service Worker for Exponit Draw
 * Scope: /lab/draw
 * Cache: lab-draw-cache-v1 (Completely isolated from ELOS root service worker)
 */

const CACHE_VERSION = 'v1';
const CACHE_NAME = `lab-draw-cache-${CACHE_VERSION}`;

const PRECACHE_ASSETS = [
    '/lab/draw',
    '/lab-draw/manifest.webmanifest',
    '/lab-draw/css/draw.css',
    '/lab-draw/js/app.js',
    '/lab-draw/js/audio.js',
    '/lab-draw/js/canvas.js',
    '/lab-draw/js/classifier.js',
    '/lab-draw/js/debug.js',
    '/lab-draw/js/game.js',
    '/lab-draw/js/pwa.js',
    '/lab-draw/js/tf.min.js',
    '/lab-draw/model/model.json',
    '/lab-draw/model/group1-shard1of1.bin',
    '/lab-draw/model/class_names.txt',
    '/lab-draw/assets/icons/icon-192.png',
    '/lab-draw/assets/icons/icon-512.png'
];

// Install: Precache all essential offline assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            console.log(`[LabDraw SW] Precaching assets for ${CACHE_NAME}...`);
            await Promise.allSettled(
                PRECACHE_ASSETS.map((url) =>
                    cache.add(url).catch((err) => {
                        console.warn(`[LabDraw SW] Precache failed for ${url}:`, err);
                    })
                )
            );
        })
    );
    self.skipWaiting();
});

// Activate: Purge obsolete lab-draw caches while leaving ELOS caches untouched
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key.startsWith('lab-draw-') && key !== CACHE_NAME) {
                        console.log(`[LabDraw SW] Removing stale cache: ${key}`);
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch: Cache-first for instant offline execution
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Intercept requests for /lab/draw page and /lab-draw/ static assets
    if (!url.pathname.startsWith('/lab/draw') && !url.pathname.startsWith('/lab-draw/')) {
        return;
    }

    // 1. Navigation requests (/lab/draw HTML)
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            caches.match('/lab/draw').then((cached) => {
                const networkFetch = fetch(request)
                    .then((networkRes) => {
                        if (networkRes && networkRes.status === 200) {
                            const clone = networkRes.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put('/lab/draw', clone));
                        }
                        return networkRes;
                    })
                    .catch(() => {
                        // Offline network failure - handled by cached response
                    });

                return cached || networkFetch;
            })
        );
        return;
    }

    // 2. Static Assets (JS, CSS, TFJS, Model weights, JSON, Icons)
    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(request)
                .then((networkRes) => {
                    if (networkRes && networkRes.status === 200) {
                        const clone = networkRes.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return networkRes;
                })
                .catch((err) => {
                    console.warn(`[LabDraw SW] Fetch failed for ${url.pathname}:`, err);
                });
        })
    );
});

// Message listener for client coordination
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
