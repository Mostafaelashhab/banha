/**
 * Hotwire Turbo — minimal native-feel setup.
 *
 * Goals:
 *   • Navigation must feel INSTANT. No fade, no slide, no progress bar.
 *     Native apps don't animate every screen change — neither do we.
 *   • The body swap is invisible because the persistent bottom-nav doesn't
 *     move and the new content drops in immediately.
 *   • A light tap-haptic on link click gives the "registered" feedback that
 *     native apps deliver via UIKit/Material.
 *
 * Edge-case wiring:
 *   • Progress bar: hidden via CSS (.turbo-progress-bar) AND we set a huge
 *     delay so it would never spawn even if CSS leaked.
 *   • Scroll: Turbo handles restore for back/forward visits; for new visits
 *     it scrolls to top, which is the right default.
 *   • Bottom-nav active state + app-badge sync re-run on `turbo:load`.
 */
import * as Turbo from '@hotwired/turbo';

Turbo.session.drive = true;
// Effectively disables the progress bar (CSS hides it; this is belt+braces)
Turbo.setProgressBarDelay(60000);

// ── Tap haptic on link clicks — feels native, costs ~5ms ──
document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    // Skip non-Turbo links (external, blank target, downloads)
    if (a.target === '_blank' || a.hasAttribute('download')) return;
    if (a.dataset.turbo === 'false') return;
    try { navigator.vibrate?.(5); } catch (_) {}
    try { window.Capacitor?.Plugins?.Haptics?.impact?.({ style: 'LIGHT' }); } catch (_) {}
}, { capture: true });

// ── Page-ready event the rest of the modules listen to ──
['DOMContentLoaded', 'turbo:load'].forEach((evt) => {
    document.addEventListener(evt, () => {
        document.dispatchEvent(new CustomEvent('banhawy:pageReady'));
        // After Turbo swaps, scroll to top for new visits is automatic — we
        // just make sure nothing weird leaks from previous transitions.
        document.documentElement.classList.remove('turbo-leaving', 'turbo-entering');
    });
});

// ── Bottom-nav active state: the nav is data-turbo-permanent, so its
//    is-active class never updates from the server after the first load.
//    We update it client-side after every navigation. ──
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
            if (linkPath === '/' || linkPath === '/feed') {
                isActive = path === '/' || path === '/feed';
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
