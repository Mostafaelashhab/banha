/**
 * App-badge — display unread notification count on the installed app icon.
 *
 * Uses the Badging API:
 *   navigator.setAppBadge(n)   → shows the dot/number on the launcher icon
 *   navigator.clearAppBadge()  → removes it
 *
 * Supported on: Chrome/Edge (desktop + Android 81+), Safari iOS 16.4+.
 *
 * The unread count is read from <body data-unread="N"> which Laravel renders
 * on every page (in the layout). On the notifications page we clear the badge
 * since the user has now seen everything. The service worker bumps the badge
 * when a push notification arrives while the page is closed.
 */
(function () {
    if (typeof navigator === 'undefined') return;
    if (typeof navigator.setAppBadge !== 'function') return;   // unsupported browser
    if (typeof document === 'undefined') return;

    const STORAGE_KEY = 'banhawy:badge:n';

    /** Read the unread count from body[data-unread] (rendered by Laravel). */
    function readServerCount() {
        const raw = document.body?.dataset.unread;
        if (raw == null || raw === '') return 0;
        const n = parseInt(raw, 10);
        return Number.isFinite(n) && n >= 0 ? n : 0;
    }

    /** Local store survives reloads — the SW can read/write it too. */
    function getStoredCount() {
        try { return Math.max(0, parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10) || 0); }
        catch (e) { return 0; }
    }
    function setStoredCount(n) {
        try { localStorage.setItem(STORAGE_KEY, String(Math.max(0, n))); } catch (e) {}
    }

    function apply(n) {
        try {
            if (n > 0) navigator.setAppBadge(n).catch(() => {});
            else       navigator.clearAppBadge?.().catch(() => {});
        } catch (e) {}
    }

    // ── Re-run on every page (initial + Turbo navigations). The body's
    //    data-unread attribute is re-rendered by Laravel on each visit. ──
    function sync() {
        const serverCount = readServerCount();
        setStoredCount(serverCount);
        apply(serverCount);
        if (location.pathname.startsWith('/notifications')) {
            setStoredCount(0);
            apply(0);
        }
    }
    sync();
    document.addEventListener('banhawy:pageReady', sync);

    // ── When the SW receives a push it posts a message; bump the badge ──
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            const data = event.data || {};
            if (data.type === 'BADGE_INCREMENT') {
                const next = getStoredCount() + 1;
                setStoredCount(next);
                apply(next);
            } else if (data.type === 'BADGE_SET') {
                const n = Math.max(0, parseInt(data.value, 10) || 0);
                setStoredCount(n);
                apply(n);
            } else if (data.type === 'BADGE_CLEAR') {
                setStoredCount(0);
                apply(0);
            }
        });
    }

    // ── Expose tiny API for the page to clear after marking-as-read actions ──
    window.banhawyBadge = {
        set(n)   { const v = Math.max(0, n|0); setStoredCount(v); apply(v); },
        clear()  { setStoredCount(0); apply(0); },
        bump(by) { const v = getStoredCount() + (by|0); setStoredCount(v); apply(v); },
    };
})();
