/* Banhawy service worker — v4
 * Strategy: cache STATIC assets + /offline page. Never cache authenticated HTML.
 */
const CACHE = 'banhawy-static-v4';
const OFFLINE_URL = '/offline';
const STATIC_ASSETS = [
    '/icons/icon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
    '/manifest.json',
    OFFLINE_URL,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((c) => c.addAll(STATIC_ASSETS))
            .catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        // Drop ALL old caches (any v1/v2 versions get nuked)
        const keys = await caches.keys();
        await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
        // Enable navigation preload (faster initial loads)
        if (self.registration.navigationPreload) {
            try { await self.registration.navigationPreload.enable(); } catch (e) {}
        }
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== location.origin) return;

    // ─── Static assets: stale-while-revalidate ─────────────
    const isStatic =
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.json' ||
        url.pathname === '/favicon.ico';

    if (isStatic) {
        event.respondWith((async () => {
            const cache  = await caches.open(CACHE);
            const cached = await cache.match(request);
            const network = fetch(request).then((res) => {
                if (res && res.status === 200) cache.put(request, res.clone());
                return res;
            }).catch(() => cached);
            return cached || network;
        })());
        return;
    }

    // ─── Everything else (HTML pages, API JSON): network-only with preload ──
    // We do NOT cache authenticated HTML to avoid showing one user's data to another.
    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                // Use navigation preload if available (much faster on cold start)
                const preload = await event.preloadResponse;
                if (preload) return preload;
                return await fetch(request);
            } catch (err) {
                // Offline fallback: serve our pre-cached /offline page
                const cache = await caches.open(CACHE);
                const offline = await cache.match(OFFLINE_URL);
                if (offline) return offline;
                return new Response(
                    '<!doctype html><meta charset="utf-8"><title>أوفلاين · بنهاوي</title>' +
                    '<style>body{font-family:Cairo,sans-serif;background:#FFF7F1;color:#0B0B0C;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px;text-align:center}</style>' +
                    '<h1>مفيش نت</h1><p>افتح النت تاني وحدّث الصفحة.</p>',
                    { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            }
        })());
        return;
    }

    // For non-navigation GET (e.g. fetch() calls), don't intercept at all.
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
