const CACHE_VERSION = 'v3';
const STATIC_CACHE_NAME = `elos-static-${CACHE_VERSION}`;
const PAGES_CACHE_NAME = `elos-pages-${CACHE_VERSION}`;

// Core application shell routes and assets to precache immediately
const PRECACHE_ASSETS = [
  '/manifest.json',
  '/icon-192.png',
  '/icon-512.png',
  '/elos/login',
  '/elos/dcr',
  '/elos/doctors',
  '/elos/doctors/create',
  '/elos/dcrs',
];

// Install Event: Precache app shell
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME).then((cache) => {
      return Promise.allSettled(
        PRECACHE_ASSETS.map((url) =>
          cache.add(url).catch((err) => {
            console.warn(`[ServiceWorker] Precache failed for ${url}:`, err);
          })
        )
      );
    })
  );
  // Force active service worker immediately without waiting
  self.skipWaiting();
});

// Activate Event: Clean up outdated cache versions
self.addEventListener('activate', (event) => {
  const allowedCaches = [STATIC_CACHE_NAME, PAGES_CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((name) => {
          if (!allowedCaches.includes(name)) {
            console.log(`[ServiceWorker] Removing old cache: ${name}`);
            return caches.delete(name);
          }
        })
      );
    })
  );
  // Ensure the service worker controls all open tabs immediately
  self.clients.claim();
});

// Fetch Event: Intelligent Offline Caching Strategies
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // 1. Only handle GET requests (mutations go through Dexie outbox queue)
  if (request.method !== 'GET') {
    return;
  }

  // 2. Never intercept API sync routes or Filament admin console
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/console/')) {
    return;
  }

  // 3. Static Assets (Vite build JS/CSS, fonts, images, webp):
  //    Strategy: Cache-First with Stale-While-Revalidate (0ms load time)
  if (
    url.pathname.startsWith('/build/') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.woff2') ||
    url.pathname.endsWith('.woff') ||
    url.pathname.endsWith('.ttf') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.webp') ||
    url.pathname.endsWith('.jpg')
  ) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        // Return cached immediately if available
        const fetchPromise = fetch(request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              const clone = networkResponse.clone();
              caches.open(STATIC_CACHE_NAME).then((cache) => cache.put(request, clone));
            }
            return networkResponse;
          })
          .catch(() => {
            // Network failure - silently ignored since cache is already served
          });

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // 4. HTML Navigation Requests (The ELOS Portal pages: /elos/dcr, /elos/doctors, etc.):
  //    Strategy: Network with quick 2.5s timeout, falling back to Stale-While-Revalidate cache
  if (
    request.mode === 'navigate' ||
    request.headers.get('accept')?.includes('text/html')
  ) {
    event.respondWith(
      new Promise((resolve) => {
        let timedOut = false;
        const timeoutId = setTimeout(() => {
          timedOut = true;
          // Timed out (e.g. flight mode or dead zone) -> fall back to cache immediately
          caches.match(request).then((cached) => {
            if (cached) {
              resolve(cached);
            } else {
              caches.match('/elos/dcr').then((fallback) => resolve(fallback));
            }
          });
        }, 2500);

        fetch(request)
          .then((networkResponse) => {
            clearTimeout(timeoutId);
            if (!timedOut && networkResponse && networkResponse.status === 200) {
              const clone = networkResponse.clone();
              caches.open(PAGES_CACHE_NAME).then((cache) => cache.put(request, clone));
              resolve(networkResponse);
            } else if (!timedOut) {
              // Status not 200 (e.g. 500 or redirect), check if cached exists
              caches.match(request).then((cached) => {
                resolve(cached || networkResponse);
              });
            }
          })
          .catch(() => {
            clearTimeout(timeoutId);
            if (!timedOut) {
              // Network error (offline) -> match cache
              caches.match(request).then((cached) => {
                if (cached) {
                  resolve(cached);
                } else {
                  // Fall back to main DCR portal shell
                  caches.match('/elos/dcr').then((fallback) => {
                    resolve(fallback || new Response('Offline Mode - Please open cached routes.', {
                      status: 503,
                      headers: { 'Content-Type': 'text/plain' }
                    }));
                  });
                }
              });
            }
          });
      })
    );
    return;
  }
});
