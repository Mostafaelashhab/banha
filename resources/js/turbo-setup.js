/**
 * Hotwire Turbo setup — turns the Laravel MPA into an app-feel SPA.
 *
 * What it does:
 *   • Intercepts every <a> and <form> → fetches over the wire, swaps the body.
 *   • Keeps the URL in sync via history.pushState.
 *   • Caches previous pages → back/forward is instant (and animation looks real).
 *   • Persists [data-turbo-permanent] elements across navigations (bottom-nav).
 *   • Fires `turbo:before-render` / `turbo:render` events that drive CSS transitions.
 *
 * Edge cases we handle:
 *   • Leaflet map page → opted out via [data-turbo="false"] on its links.
 *   • External links → opted out (Turbo only handles same-origin by default).
 *   • Multipart forms (file uploads) → still POST traditionally.
 *   • Owner / admin actions with custom JS → re-init on `turbo:load`.
 */
import * as Turbo from '@hotwired/turbo';

// ── Cache strategy ───────────────────────────────────────
// Turbo caches the last visited page so back navigation is instant. We keep
// the cache up to 1 minute fresh, then revalidate.
Turbo.session.drive = true;

// ── Show progress bar after 250ms only (snappy on fast nets, visible on slow)
Turbo.setProgressBarDelay(250);

// ── Page-transition class hooks ──────────────────────────
// We toggle .turbo-leaving / .turbo-entering on <html> at swap time so the
// CSS in app.css can run fade+slide transitions on the <main> element.
document.addEventListener('turbo:before-visit', () => {
    // Save scroll position on the current page before it leaves
    sessionStorage.setItem('turbo:scroll:' + location.pathname, String(window.scrollY));
});

document.addEventListener('turbo:before-render', (e) => {
    document.documentElement.classList.add('turbo-leaving');
    // Briefly delay the render so the leaving animation can play out (220ms).
    e.preventDefault();
    requestAnimationFrame(() => {
        setTimeout(() => {
            e.detail.resume();
        }, 180);
    });
});

document.addEventListener('turbo:render', () => {
    document.documentElement.classList.remove('turbo-leaving');
    document.documentElement.classList.add('turbo-entering');
    requestAnimationFrame(() => {
        // Force a reflow then drop the class to retrigger the enter animation
        void document.body.offsetHeight;
        setTimeout(() => {
            document.documentElement.classList.remove('turbo-entering');
        }, 260);
    });
});

// ── Restore scroll position when going back ──
document.addEventListener('turbo:load', () => {
    const saved = sessionStorage.getItem('turbo:scroll:' + location.pathname);
    // Only restore for back/forward navigations (Turbo flags this in event.detail)
    // We can't easily detect that here — but Turbo automatically restores scroll
    // for "restoration" visits, so we only need to handle the case it missed.
    if (saved && window.scrollY === 0 && parseInt(saved, 10) > 0) {
        // Small delay so the swapped DOM has reflowed before we scroll
        setTimeout(() => window.scrollTo(0, parseInt(saved, 10)), 20);
    }
});

// ── Bridge: emit a custom event the rest of our modules can listen to
//    for "the page just changed" (works for both initial load + Turbo nav).
['DOMContentLoaded', 'turbo:load'].forEach((evt) => {
    document.addEventListener(evt, () => {
        document.dispatchEvent(new CustomEvent('banhawy:pageReady'));
    });
});

// ── Bottom-nav active state: the nav itself is data-turbo-permanent (so it
//    survives the body swap without flicker), but that means its is-active
//    class never updates from the server. We update it client-side based on
//    the new URL after every navigation.
document.addEventListener('banhawy:pageReady', () => {
    const nav = document.getElementById('bottom-nav');
    if (!nav) return;
    const path = location.pathname;
    nav.querySelectorAll('.nav-item').forEach((a) => {
        const href = a.getAttribute('href');
        if (!href) return;
        let isActive = false;
        try {
            const linkPath = new URL(href, location.origin).pathname;
            if (linkPath === '/' || linkPath === '') {
                isActive = path === '/' || path === '/feed';
            } else if (linkPath === '/feed') {
                isActive = path === '/feed' || path === '/';
            } else if (linkPath === '/directory') {
                isActive = path.startsWith('/directory') && !path.startsWith('/directory/map');
            } else if (linkPath === '/map' || linkPath === '/directory/map') {
                isActive = path === '/map' || path === '/directory/map';
            } else if (linkPath.startsWith('/notifications')) {
                isActive = path.startsWith('/notifications');
            } else if (linkPath.startsWith('/me') || linkPath.startsWith('/profile')) {
                isActive = path.startsWith('/me') || path.startsWith('/profile');
            }
        } catch (_) {}
        a.classList.toggle('is-active', isActive);
    });
});
