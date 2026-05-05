const CACHE = 'famfinance-v1';

// On install: activate immediately, no waiting
self.addEventListener('install', () => self.skipWaiting());

// On activate: drop old caches, claim all clients
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin GET requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    // Vite build assets have content-hash filenames — cache-first (safe forever)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then(cached => cached || fetch(request).then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE).then(c => c.put(request, clone));
                }
                return response;
            }))
        );
        return;
    }

    // Icons and manifest — cache-first
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.webmanifest') {
        event.respondWith(
            caches.match(request).then(cached => cached || fetch(request).then(response => {
                const clone = response.clone();
                caches.open(CACHE).then(c => c.put(request, clone));
                return response;
            }))
        );
        return;
    }

    // Everything else (pages, API): network-first, fallback to cache
    event.respondWith(
        fetch(request)
            .then(response => {
                if (response.ok && request.mode === 'navigate') {
                    const clone = response.clone();
                    caches.open(CACHE).then(c => c.put(request, clone));
                }
                return response;
            })
            .catch(() => caches.match(request))
    );
});
