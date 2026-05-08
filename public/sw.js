/* Banhawy service worker */
const CACHE = 'banhawy-v1';
const ASSETS = [
    '/icons/icon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
    '/manifest.json',
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE).then((c) => c.addAll(ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Stale-while-revalidate for navigation, network-first for API, cache-first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== location.origin) return;

    // Skip vite/build hashed assets — let them be (cache-busted by hash)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) =>
                cached ||
                fetch(request).then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(request, copy));
                    return res;
                }).catch(() => cached)
            )
        );
        return;
    }

    // HTML navigation: network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(request, copy));
                    return res;
                })
                .catch(() => caches.match(request).then((r) => r || caches.match('/')))
        );
        return;
    }

    // Default: cache-first
    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request))
    );
});

// ─── Push notifications ──────────────────────────────────
self.addEventListener('push', (event) => {
    let data = { title: 'بنهاوي', body: 'حصلت حاجة جديدة في حيك', url: '/feed' };
    try { if (event.data) data = { ...data, ...event.data.json() }; } catch (e) {}

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: data.tag || 'banhawy',
            data: { url: data.url },
            dir: 'rtl',
            lang: 'ar',
            vibrate: [80, 40, 80],
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/feed';
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((wins) => {
            for (const w of wins) {
                if (w.url.includes(url) && 'focus' in w) return w.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
