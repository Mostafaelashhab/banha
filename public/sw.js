/* Banhawy service worker — v7
 *
 * Native-app caching strategies:
 *   • App shell + icons        → cache-first with background refresh
 *   • Public HTML navigations  → stale-while-revalidate (instant from cache, fresh on revisit)
 *   • Authenticated pages      → network-first, NEVER cached (each user has their own data)
 *   • Same-origin /map.json    → network-first with short cache fallback
 *
 * Offline experience:
 *   • Cold launch offline    → falls back to /launch.html (which boots into cached pages)
 *   • Navigation fails       → /offline page if known, otherwise inline shell
 */
const VERSION    = 'v7';
const CACHE      = 'banhawy-static-' + VERSION;
const PAGE_CACHE = 'banhawy-pages-' + VERSION;
const LAUNCH_URL  = '/launch.html';
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

/** Pages we'll opportunistically cache as users browse them.
    Authenticated pages are detected by the `laravel_session` cookie + the
    Set-Cookie response header — we just refuse to cache when the URL is
    inside the auth-required area. */
const CACHEABLE_NAVIGATION_RE = new RegExp(
    '^(/$|' +
    '/feed|/directory|/map|/zone|/zones|/bookings|' +
    '/m/|/biz/|/offers|/emergency|/banha-|/benha-|' +
    '/launch\\.html|/about|/contact|/privacy|/terms)'
);

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
        await Promise.all(
            keys.filter((k) => k !== CACHE && k !== PAGE_CACHE).map((k) => caches.delete(k))
        );
        if (self.registration.navigationPreload) {
            try { await self.registration.navigationPreload.enable(); } catch (e) {}
        }
        await self.clients.claim();
    })());
});

/** Allow the page to tell us "skip waiting" once the user accepts the update banner. */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
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
            if (cached) {
                event.waitUntil((async () => {
                    try {
                        const fresh = await fetch(request);
                        if (fresh && fresh.status === 200) await cache.put(request, fresh.clone());
                    } catch (e) {}
                })());
                return cached;
            }
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

    // ─── Navigations: stale-while-revalidate for known public routes,
    //   network-first with offline fallback for the rest. ────────────
    if (request.mode === 'navigate') {
        const isCacheable = CACHEABLE_NAVIGATION_RE.test(url.pathname);

        event.respondWith((async () => {
            try {
                const preload = await event.preloadResponse;
                if (preload) {
                    // Only cache the response if it's clearly public (no Set-Cookie)
                    // and the URL is in our cacheable list.
                    maybeCachePage(preload.clone(), request, isCacheable);
                    return preload;
                }
            } catch (e) {}

            const cache = await caches.open(PAGE_CACHE);

            // Try network first, fall back to cache, then to offline page.
            try {
                const fresh = await fetch(request);
                maybeCachePage(fresh.clone(), request, isCacheable);
                return fresh;
            } catch (err) {
                const cached = isCacheable ? await cache.match(request) : null;
                if (cached) return cached;

                const launch = await caches.match(LAUNCH_URL);
                if (launch) return launch;

                const offline = await caches.match(OFFLINE_URL);
                if (offline) return offline;

                return new Response(inlineOfflineShell(), {
                    headers: { 'Content-Type': 'text/html; charset=utf-8' },
                });
            }
        })());
        return;
    }
});

function maybeCachePage(response, request, isCacheable) {
    if (!isCacheable || !response || response.status !== 200) return;
    // Skip caching responses that look authenticated or vary by user
    const setCookie = response.headers.get('set-cookie');
    if (setCookie && /XSRF-TOKEN|laravel_session/i.test(setCookie)) return;
    const cacheControl = response.headers.get('cache-control') || '';
    if (/no-store|private/i.test(cacheControl)) return;

    caches.open(PAGE_CACHE).then((c) => {
        c.put(request, response).catch(() => {});
        // Trim the page cache to ~40 entries so memory doesn't balloon.
        c.keys().then((keys) => {
            if (keys.length > 40) c.delete(keys[0]);
        });
    });
}

function inlineOfflineShell() {
    return '<!doctype html><meta charset="utf-8"><title>أوفلاين · بنهاوي</title>' +
        '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">' +
        '<style>' +
        ':root{color-scheme:light}' +
        'body{font-family:Cairo,system-ui,sans-serif;background:#FFF7F1;color:#0B0B0C;' +
        'display:grid;place-items:center;min-height:100dvh;margin:0;padding:24px;text-align:center}' +
        'h1{font-size:22px;margin:.5em 0}p{color:#5C5C66;font-size:14px}' +
        'button{background:#2D5BFF;color:#fff;border:0;border-radius:999px;padding:12px 24px;' +
        'font-weight:800;font-size:14px;cursor:pointer;margin-top:16px}' +
        '</style>' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="#2D5BFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:64px;height:64px">' +
        '<path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/>' +
        '<path d="M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0"/>' +
        '<line x1="12" y1="20" x2="12.01" y2="20"/></svg>' +
        '<h1>مفيش نت</h1>' +
        '<p>افتح النت تاني عشان تحدّث الصفحة. الصفحات اللي زرتها قبل كده بتظهر برضو من الكاش.</p>' +
        '<button onclick="location.reload()">حاول تاني</button>';
}

// ─── Push notifications ──────────────────────────────────
self.addEventListener('push', (event) => {
    let data = { title: 'بنهاوي', body: 'حصلت حاجة جديدة في حيك', url: '/feed' };
    try { if (event.data) data = { ...data, ...event.data.json() }; } catch (e) {}

    event.waitUntil((async () => {
        // 1) Show the notification
        await self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: data.tag || 'banhawy',
            data: { url: data.url },
            dir: 'rtl',
            lang: 'ar',
            vibrate: [80, 40, 80],
            renotify: !!data.tag,
        });

        // 2) Bump the home-screen app badge. Prefer the explicit count from
        //    the push payload (server-authoritative). Otherwise let the page
        //    increment its own stored counter via postMessage.
        try {
            if (typeof data.unread === 'number' && self.navigator?.setAppBadge) {
                await self.navigator.setAppBadge(Math.max(0, data.unread));
            } else if (self.navigator?.setAppBadge) {
                // No count given — bump by 1 via the page if it's open, else
                // best-effort set to 1 (better than no badge at all).
                const wins = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
                if (wins.length) {
                    wins.forEach((w) => w.postMessage({ type: 'BADGE_INCREMENT' }));
                } else {
                    await self.navigator.setAppBadge(1);
                }
            }
        } catch (e) {}
    })());
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/feed';
    event.waitUntil((async () => {
        // Clearing on click would be premature — the user might not have viewed
        // the full list yet. We clear when they actually land on /notifications.
        const wins = await clients.matchAll({ type: 'window', includeUncontrolled: true });
        for (const w of wins) {
            if (w.url.includes(url) && 'focus' in w) return w.focus();
        }
        if (clients.openWindow) return clients.openWindow(url);
    })());
});
