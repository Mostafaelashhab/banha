/**
 * Edge-swipe-back — iOS-style "two screens stacked" back gesture.
 *
 * How it feels:
 *   1. User starts a touch near the LEFT edge of the screen.
 *   2. As they drag right, the current page lifts off and slides to the right.
 *      Behind it, a dim layer reveals the page underneath.
 *   3. Past ~38% of the viewport, releasing commits to `history.back()`.
 *   4. Below threshold, the page springs back into place.
 *
 * The "page underneath" is the browser's bfcache — when we call history.back(),
 * the previous page is restored instantly, completing the illusion of two
 * stacked screens.
 *
 * Smoothness: every drag frame is rAF-batched. All animation is `transform` +
 * `opacity` only, so the GPU does the compositing.
 */
(function () {
    if (typeof window === 'undefined') return;
    if (!matchMedia('(hover: none) and (pointer: coarse)').matches) return;
    if (document.documentElement.dataset.edgeSwipe === 'off') return;

    // Don't activate on the very first page (nothing to go back to).
    // Also skip if there's only one entry in history.
    function canGoBack() {
        // history.length is unreliable across reloads; we also accept if the
        // referrer is same-origin which is a strong "came from elsewhere" hint.
        if (history.length > 1) return true;
        try { return new URL(document.referrer).origin === location.origin; } catch (_) { return false; }
    }

    // Tunables
    const EDGE_ZONE_PX     = 28;     // start the gesture only this close to the edge
    const ACTIVATE_DX_PX   = 8;      // ignore micro-jitter
    const COMMIT_FRACTION  = 0.38;   // release past 38% of viewport width = navigate back
    const COMMIT_VELOCITY  = 0.55;   // OR release with this px/ms velocity = commit
    const DEPTH_SCALE      = 0.92;   // how small the underlying "previous" view appears
    const PARALLAX_RATIO   = 0.30;   // underlying view slides at 30% of finger speed

    // ── Build the overlay DOM the first time we activate ──────
    let overlay  = null;   // dim backdrop + "previous page" placeholder
    let prevEl   = null;   // the placeholder that simulates the page underneath
    let mainEl   = null;   // the page contents we lift
    let edgeOnLeft = true; // false in case we ever flip RTL semantics

    function ensureOverlay() {
        if (overlay) return overlay;
        overlay = document.createElement('div');
        overlay.className = 'esb-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML =
            '<div class="esb-prev" data-prev>' +
                '<div class="esb-prev-pattern"></div>' +
            '</div>' +
            '<div class="esb-dim" data-dim></div>';
        document.body.appendChild(overlay);
        prevEl = overlay.querySelector('[data-prev]');
        return overlay;
    }

    function ensureMainTarget() {
        if (mainEl) return mainEl;
        // Lift the <main> element if present (Laravel layout), otherwise body.
        mainEl = document.querySelector('main') || document.body;
        return mainEl;
    }

    // ── State ────────────────────────────────────────────────
    let startX = null;
    let startY = null;
    let startT = 0;
    let lastX  = 0;
    let lastT  = 0;
    let active = false;       // gesture committed (not just a tap)
    let raf    = 0;
    let progress = 0;         // 0..1 (0 = at rest, 1 = fully swiped)
    let committing = false;

    function vw() { return window.innerWidth || document.documentElement.clientWidth || 360; }

    function shouldStart(t) {
        if (committing) return false;
        if (!canGoBack()) return false;
        if (!t || !t.closest) return false;
        if (t.closest('input, textarea, select, [contenteditable="true"]')) return false;
        if (t.closest('[data-no-edge-swipe]')) return false;
        if (t.closest('#banha-map, .leaflet-container')) return false;
        // Open modals/sheets — they have their own gestures
        if (document.body.classList.contains('lb-locked')) return false;
        if (document.body.style.overflow === 'hidden') return false;
        if (document.querySelector('[data-cart-sheet]:not(.hidden)')) return false;
        return true;
    }

    function paint() {
        raf = 0;
        if (!active) return;
        const w  = vw();
        const dx = Math.max(0, lastX - startX);
        progress = Math.min(1, dx / w);

        // Slight resistance past 1.0 (won't happen often)
        const tx = dx;

        // ── The current (foreground) page lifts and slides off to the right.
        const main = ensureMainTarget();
        main.style.transform =
            'translate3d(' + tx.toFixed(1) + 'px, 0, 0) ' +
            'scale(' + (1 - progress * 0.04).toFixed(3) + ')';
        main.style.boxShadow = 'rgba(11, 11, 12, ' + (0.25 - progress * 0.1).toFixed(2) + ') ' +
                               '-24px 0 48px -8px';

        // ── The "previous page" underneath slides in from the left at parallax speed.
        const prevTx = -w * (1 - progress) * PARALLAX_RATIO;
        const prevScale = DEPTH_SCALE + (1 - DEPTH_SCALE) * progress;
        if (prevEl) {
            prevEl.style.transform =
                'translate3d(' + prevTx.toFixed(1) + 'px, 0, 0) scale(' + prevScale.toFixed(3) + ')';
            prevEl.style.opacity = String(0.55 + progress * 0.45);
        }
        // Dim layer between the two — fades out as the previous page comes forward.
        const dim = overlay.querySelector('[data-dim]');
        if (dim) dim.style.opacity = String(0.35 * (1 - progress));
    }

    function schedulePaint() {
        if (raf) return;
        raf = requestAnimationFrame(paint);
    }

    function reset(animated) {
        const main = ensureMainTarget();
        if (animated) {
            main.classList.add('esb-anim');
            if (overlay) overlay.classList.add('esb-anim');
        }
        main.style.transform = '';
        main.style.boxShadow = '';
        if (prevEl) {
            prevEl.style.transform = '';
            prevEl.style.opacity = '';
        }
        if (overlay) {
            const dim = overlay.querySelector('[data-dim]');
            if (dim) dim.style.opacity = '';
        }
        // Cleanup classes after the animation runs
        setTimeout(() => {
            main.classList.remove('esb-anim');
            overlay?.classList.remove('esb-anim');
            // Hide overlay until next gesture
            overlay?.classList.remove('is-active');
        }, animated ? 320 : 0);

        active = false;
        startX = null;
        startY = null;
        progress = 0;
        document.documentElement.classList.remove('esb-active');
    }

    function commit() {
        if (committing) return;
        committing = true;
        const main = ensureMainTarget();
        const w = vw();
        // Animate the rest of the way off-screen, then trigger history.back().
        main.classList.add('esb-anim');
        if (overlay) overlay.classList.add('esb-anim');
        main.style.transform = 'translate3d(' + w + 'px, 0, 0) scale(.96)';
        if (prevEl) {
            prevEl.style.transform = 'translate3d(0, 0, 0) scale(1)';
            prevEl.style.opacity = '1';
        }
        const dim = overlay?.querySelector('[data-dim]');
        if (dim) dim.style.opacity = '0';

        // Give the animation ~280ms to play, then go back. The bfcache restores
        // the previous page in place — looks like the underlying screen took over.
        setTimeout(() => {
            try { history.back(); } catch (_) {}
            // Whether or not back fires, clear our state so a refresh isn't broken.
            setTimeout(() => {
                committing = false;
                main.classList.remove('esb-anim');
                main.style.transform = '';
                main.style.boxShadow = '';
                overlay?.classList.remove('is-active', 'esb-anim');
            }, 200);
        }, 260);
    }

    // ── Touch handlers ───────────────────────────────────────
    document.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        const t = e.touches[0];
        // Edge zone: left edge (RTL/LTR irrelevant — iOS uses the leading edge)
        const inEdge = edgeOnLeft ? t.clientX <= EDGE_ZONE_PX : t.clientX >= vw() - EDGE_ZONE_PX;
        if (!inEdge) return;
        if (!shouldStart(e.target)) return;

        startX = t.clientX;
        startY = t.clientY;
        startT = performance.now();
        lastX  = t.clientX;
        lastT  = startT;
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (startX === null) return;
        const t = e.touches[0];
        const dx = t.clientX - startX;
        const dy = t.clientY - startY;

        // If the gesture is more vertical than horizontal, abort — let the page scroll.
        if (!active && Math.abs(dy) > Math.abs(dx) + 4) {
            startX = startY = null;
            return;
        }

        if (!active && dx > ACTIVATE_DX_PX) {
            active = true;
            ensureOverlay().classList.add('is-active');
            ensureMainTarget().classList.remove('esb-anim');
            overlay?.classList.remove('esb-anim');
            document.documentElement.classList.add('esb-active');
        }

        if (active) {
            lastX = t.clientX;
            lastT = performance.now();
            schedulePaint();
            if (e.cancelable) e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        if (startX === null) return;
        if (!active) { reset(false); return; }

        const w        = vw();
        const dx       = Math.max(0, lastX - startX);
        const velocity = Math.abs(lastX - startX) / Math.max(performance.now() - startT, 1); // px/ms

        if (dx / w >= COMMIT_FRACTION || velocity >= COMMIT_VELOCITY) {
            commit();
        } else {
            reset(true);
        }
    }, { passive: true });

    document.addEventListener('touchcancel', () => reset(true), { passive: true });
})();
