// sw.js
const CACHE = "mautresor-v1";
const URLS = ["/offline.html", "/manifest.json", "/assets/css/main.css", "/assets/js/app.js"]; // optional (create it or remove related lines)

const ASSET_EXT = /\.(?:css|js|mjs|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|otf|eot|map)(?:\?.*)?$/i;

self.addEventListener("install", (e) => {
    e.waitUntil(
        caches.open(CACHE).then((c) => c.addAll(URLS).catch(() => {}))
    );
    self.skipWaiting();
});

self.addEventListener("activate", (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.map((k) => (k === CACHE ? null : caches.delete(k))))
        )
    );
    self.clients.claim();
});

self.addEventListener("fetch", (e) => {
    const req = e.request;
    const url = new URL(req.url);

    // Only handle same-origin GET requests
    if (req.method !== "GET" || url.origin !== location.origin) return;

    // 1) Cache-first for static assets
    if (ASSET_EXT.test(url.pathname)) {
        e.respondWith(
            caches.match(req).then((hit) => {
                if (hit) return hit;
                return fetch(req).then((res) => {
                    // Cache successful basic responses
                    if (res.ok && res.type === "basic") {
                        const copy = res.clone();
                        caches.open(CACHE).then((c) => c.put(req, copy));
                    }
                    return res;
                });
            })
        );
        return;
    }

    // 2) Network-first for navigations (HTML)
    if (req.mode === "navigate") {
        e.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(req, copy));
                    return res;
                })
                .catch(() => caches.match(req).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // 3) Default: cache-first fallback to network
    e.respondWith(caches.match(req).then((hit) => hit || fetch(req)));
});

/* ---------- Notifications / Push ---------- */

// Works with Web Push (server -> push service -> SW)
self.addEventListener("push", (e) => {
    let data = {};
    try { data = e.data ? e.data.json() : {}; } catch (_) {}

    const title = data.title || "Notification";
    const options = {
        body: data.body || "",
        icon: data.icon || "/assets/img/icon-192.png",
        badge: data.badge || "/assets/img/icon-192.png",
        data: { url: data.url || "/" }
    };

    e.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (e) => {
    e.notification.close();
    const url = (e.notification.data && e.notification.data.url) || "/";

    e.waitUntil((async () => {
        const all = await clients.matchAll({ type: "window", includeUncontrolled: true });
        for (const c of all) {
            if ("focus" in c) return c.focus();
        }
        if (clients.openWindow) return clients.openWindow(url);
    })());
});
