/**
 * Exponit Draw — PWA & Service Worker Manager
 * Handles dedicated scoped registration (/lab/draw) and offline readiness validation.
 */

const CACHE_NAME = 'lab-draw-cache-v1';

const CRITICAL_OFFLINE_URLS = [
    '/lab/draw',
    '/lab-draw/css/draw.css',
    '/lab-draw/js/app.js',
    '/lab-draw/js/tf.min.js',
    '/lab-draw/model/model.json',
    '/lab-draw/model/group1-shard1of1.bin',
    '/lab-draw/model/class_names.txt',
    '/lab-draw/manifest.webmanifest'
];

export async function isOfflineReady() {
    if (!('serviceWorker' in navigator) || !('caches' in window)) {
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.getRegistration('/lab/draw');
        if (!registration || !registration.active) {
            return false;
        }

        const cache = await caches.open(CACHE_NAME);
        const matches = await Promise.all(CRITICAL_OFFLINE_URLS.map(url => cache.match(url)));
        const allCached = matches.every(res => res !== undefined && res.status === 200);

        return allCached;
    } catch (e) {
        console.warn('[PWA] Offline readiness check error:', e);
        return false;
    }
}

export async function initPWA(onStatusUpdate = null) {
    if (!('serviceWorker' in navigator)) {
        if (onStatusUpdate) {
            onStatusUpdate({
                swStatus: 'UNSUPPORTED',
                offlineReady: 'NO (NO SW)'
            });
        }
        return null;
    }

    try {
        // Register dedicated service worker with explicit /lab/draw scope
        const registration = await navigator.serviceWorker.register('/lab-draw/sw.js', {
            scope: '/lab/draw'
        });

        const updateStatus = async () => {
            const state = registration.installing ? 'INSTALLING' :
                          registration.waiting ? 'WAITING' :
                          registration.active ? 'ACTIVE' : 'IDLE';

            const ready = await isOfflineReady();

            if (onStatusUpdate) {
                onStatusUpdate({
                    swStatus: state,
                    offlineReady: ready ? 'YES ✓' : 'CACHING...'
                });
            }

            return ready;
        };

        // Listen for worker state changes
        if (registration.installing) {
            registration.installing.addEventListener('statechange', (e) => {
                if (e.target.state === 'activated') {
                    updateStatus();
                }
            });
        }

        // Periodic or event-driven check
        await updateStatus();

        // Check again after 2 seconds to confirm cache population
        setTimeout(updateStatus, 2000);

        return registration;
    } catch (err) {
        console.warn('[PWA] Service worker registration failed:', err);
        if (onStatusUpdate) {
            onStatusUpdate({
                swStatus: 'ERROR',
                offlineReady: 'NO'
            });
        }
        return null;
    }
}
