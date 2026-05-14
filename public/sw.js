/* Banhawy service worker — v6
 * Strategy:
 *   • Pre-cache launch shell + static assets (icons, manifest, offline page)
 *   • Static assets (icons, /build/*) → cache-first with background refresh
 *   • Navigations → navigationPreload + cached launch shell as fast fallback
 *   • Authenticated HTML is never cached cross-user (we only cache the launch shell, which has no per-user data)
 */
const CACHE = 'banhawy-static-v6';
const LAUNCH_URL = '/launch.html';
const OFFLINE_URL = '/offline';
const STATIC_ASSETS = [
    LAUNCH_URL,
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
        const keys = await caches.keys();
        await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
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

    // ─── Static assets: cache-first, refresh in background ─────────
    const isStatic =
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.json' ||
        url.pathname === '/favicon.ico' ||
        url.pathname === LAUNCH_URL;

    if (isStatic) {
        event.respondWith((async () => {
            const cache  = await caches.open(CACHE);
            const cached = await cache.match(request);
            // If we have it cached, serve immediately and refresh in background
            if (cached) {
                event.waitUntil((async () => {
                    try {
                        const fresh = await fetch(request);
                        if (fresh && fresh.status === 200) await cache.put(request, fresh.clone());
                    } catch (e) {}
                })());
                return cached;
            }
            // Not cached yet — go to network, cache the result
            try {
                const fresh = await fetch(request);
                if (fresh && fresh.status === 200) cache.put(request, fresh.clone());
                return fresh;
            } catch (e) {
                return new Response('', { status: 504 });
            }
        })());
        return;
    }

    // ─── Navigations: preload, with launch-shell fallback while booting ─────
    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preload = await event.preloadResponse;
                if (preload) return preload;
                return await fetch(request);
            } catch (err) {
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
