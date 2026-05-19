/**
 * Bottom-nav auto-hide on scroll.
 *
 * Behavior (native-app style):
 *   • Scroll DOWN past a threshold  → nav slides off-screen.
 *   • Scroll UP                     → nav slides back in immediately.
 *   • Scroll stops for ~600ms       → nav slides back in.
 *   • Near the top of the page      → always visible.
 *   • While a modal/sheet is open   → nav stays visible (gestures inside the
 *                                     sheet shouldn't fight the nav).
 *
 * rAF-batched so the 60fps scroll never stutters.
 */
(function () {
    if (typeof window === 'undefined') return;

    const nav = document.querySelector('.bottom-nav');
    if (!nav) return;

    // Tunables
    const HIDE_AFTER_DOWN_PX = 24;  // must scroll down at least this much to hide
    const SHOW_AFTER_UP_PX   = 6;   // tiniest upward scroll re-shows
    const ALWAYS_VISIBLE_TOP = 80;  // near the top of the page, always show
    const IDLE_RESHOW_MS     = 600; // when scrolling stops, re-show after this

    let lastY        = window.scrollY || 0;
    let accumDown    = 0;
    let accumUp      = 0;
    let ticking      = false;
    let idleTimer    = 0;
    let suppressed   = false; // forced-visible state during e.g. modal-open

    function isModalOrSheetOpen() {
        // Lightbox lock or any open bottom-sheet (cart, etc.)
        if (document.body.classList.contains('lb-locked')) return true;
        if (document.body.style.overflow === 'hidden') return true;
        // Open data-cart-sheet without .hidden
        const cartSheet = document.querySelector('[data-cart-sheet]:not(.hidden)');
        if (cartSheet) return true;
        return false;
    }

    function show() {
        nav.classList.remove('is-hidden');
    }
    function hide() {
        nav.classList.add('is-hidden');
    }

    function onScrollRaf() {
        ticking = false;
        const y = window.scrollY || document.documentElement.scrollTop || 0;
        const dy = y - lastY;
        lastY = y;

        // Near-top guard
        if (y < ALWAYS_VISIBLE_TOP) {
            accumDown = accumUp = 0;
            show();
            scheduleIdleReshow();
            return;
        }

        if (isModalOrSheetOpen()) {
            show();
            return;
        }

        if (dy > 0) {
            // Scrolling down — accumulate; hide once we've gone past the threshold.
            accumDown += dy;
            accumUp = 0;
            if (accumDown > HIDE_AFTER_DOWN_PX) hide();
        } else if (dy < 0) {
            // Scrolling up — show as soon as a small upward movement is detected.
            accumUp += -dy;
            accumDown = 0;
            if (accumUp > SHOW_AFTER_UP_PX) show();
        }

        scheduleIdleReshow();
    }

    function scheduleIdleReshow() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            // Scrolling has stopped — bring the nav back so the user can act.
            if (!isModalOrSheetOpen()) show();
        }, IDLE_RESHOW_MS);
    }

    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(onScrollRaf);
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    // Re-show when the user backgrounds + foregrounds the page (looks better
    // than them returning to a hidden nav from another tab).
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) show();
    });

    // Re-show right after a focus change to an input (keyboard opens), since
    // the page often jumps and we want the nav predictable.
    document.addEventListener('focusin', (e) => {
        if (e.target.matches?.('input, textarea, select, [contenteditable]')) show();
    });
})();
