const CACHE_NAME = 'mautresor-v1';
const LOCAL_ASSETS = [
    '/',
    'manifest.json'
];

self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE_NAME);

            // Cache local assets first
            await Promise.all(
                LOCAL_ASSETS.map(async url => {
                    try {
                        const res = await fetch(url);
                        if (res.ok) await cache.put(url, res.clone());
                        else console.error('[SW] Failed local fetch:', url, res.status);
                    } catch (e) {
                        console.error('[SW] Failed local fetch:', url, e);
                    }
                })
            );
        })()
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch handler example
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(cached => {
            if (cached) return cached;
            return fetch(event.request)
                .catch(err => {
                    console.error('[SW] Fetch failed:', event.request.url, err);
                    return new Response('Offline', { status: 503 });
                });
        })
    );
});