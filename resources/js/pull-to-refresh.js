/**
 * Pull-to-refresh — buttery-smooth native-style pull at the top of the page.
 *
 * Visual: a circular pill with an SVG progress ring that fills as you pull.
 * Past the threshold, a subtle scale "spring" + haptic confirms you can release
 * to refresh. The ring then spins indeterminately while we reload.
 *
 * Smoothness:
 *   • All DOM writes batched into a single requestAnimationFrame.
 *   • `transform` + `opacity` only — never width/height/top — so it's GPU-composited.
 *   • Threshold easing makes the pull feel resistant past the trigger point.
 *
 * Constraints:
 *   • Only on touch-primary devices.
 *   • Document must be scrolled to the very top.
 *   • Skipped inside inputs, the map, modals, horizontal scrollers, [data-no-ptr].
 */
(function () {
    if (typeof window === 'undefined') return;
    if (!matchMedia('(hover: none) and (pointer: coarse)').matches) return;
    if (document.documentElement.dataset.ptr === 'off') return;

    const THRESHOLD = 84;
    const MAX_PULL  = 160;
    const ACTIVATION_PX = 6;

    // SVG ring geometry (must match the markup below)
    const RING_R    = 13;
    const RING_C    = 2 * Math.PI * RING_R; // ≈ 81.68

    // ── DOM ──────────────────────────────────────────────────
    let host    = null;
    let ringEl  = null;
    let arrowEl = null;
    let pillEl  = null;

    function ensureHost() {
        if (host) return host;
        host = document.createElement('div');
        host.className = 'ptr-host';
        host.setAttribute('aria-hidden', 'true');
        host.innerHTML =
            '<div class="ptr-pill">' +
                '<svg class="ptr-ring" viewBox="0 0 32 32" aria-hidden="true">' +
                    '<circle class="ptr-ring-track" cx="16" cy="16" r="' + RING_R + '"/>' +
                    '<circle class="ptr-ring-bar"   cx="16" cy="16" r="' + RING_R + '" ' +
                            'stroke-dasharray="' + RING_C.toFixed(2) + '" ' +
                            'stroke-dashoffset="' + RING_C.toFixed(2) + '"/>' +
                '</svg>' +
                '<svg class="ptr-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
                    '<polyline points="6 9 12 15 18 9"/>' +
                '</svg>' +
            '</div>';
        document.body.appendChild(host);
        ringEl  = host.querySelector('.ptr-ring-bar');
        arrowEl = host.querySelector('.ptr-arrow');
        pillEl  = host.querySelector('.ptr-pill');
        return host;
    }

    // ── State ────────────────────────────────────────────────
    let startY      = null;
    let currentDy   = 0;
    let committed   = false;
    let refreshing  = false;
    let armed       = false;
    let rafId       = 0;

    function canStartPull(e) {
        if (refreshing) return false;
        if (window.scrollY > 0 || document.documentElement.scrollTop > 0) return false;
        const t = e.target;
        if (!t || !t.closest) return false;
        if (t.closest('input, textarea, select, [contenteditable="true"]')) return false;
        if (t.closest('[data-no-ptr]')) return false;
        if (t.closest('#banha-map, .leaflet-container')) return false;
        if (document.body.classList.contains('lb-locked')) return false;
        if (document.body.style.overflow === 'hidden') return false;
        // Walk up — if we're inside an already-scrolled scrollable, bail
        let n = t;
        while (n && n !== document.body) {
            const cs = getComputedStyle(n);
            if ((cs.overflowY === 'auto' || cs.overflowY === 'scroll' || cs.overflowX === 'auto' || cs.overflowX === 'scroll')
                && (n.scrollTop > 0 || n.scrollLeft > 0)) return false;
            n = n.parentElement;
        }
        return true;
    }

    /** Resistance easing past the threshold so the pull feels heavier. */
    function eased(dy) {
        if (dy <= THRESHOLD) return dy;
        const overshoot = dy - THRESHOLD;
        // 1 - 1 / (1 + x/D) → asymptotic resistance
        return THRESHOLD + (MAX_PULL - THRESHOLD) * (1 - 1 / (1 + overshoot / 80));
    }

    /** Schedule a single rAF that paints the latest pull frame — coalesces
     *  rapid touchmove events into one paint per refresh. */
    function schedulePaint() {
        if (rafId) return;
        rafId = requestAnimationFrame(() => {
            rafId = 0;
            paint(currentDy);
        });
    }

    function paint(dy) {
        if (!host) return;
        const e = eased(dy);
        // Translate down. Pill is 56px → -28 keeps center aligned.
        host.style.transform = 'translate3d(-50%, ' + (e - 28).toFixed(1) + 'px, 0)';
        // Fade in over the first ~40px, then full opacity.
        host.style.opacity = String(Math.min(1, dy / 40));

        // Ring progress: 0 → 1 over THRESHOLD pixels.
        const pct = Math.min(1, dy / THRESHOLD);
        const offset = RING_C * (1 - pct);
        if (ringEl) ringEl.style.strokeDashoffset = offset.toFixed(2);

        // Arrow rotates 0° → 180° as a hint that "release will refresh".
        if (arrowEl) arrowEl.style.transform = 'rotate(' + (pct * 180).toFixed(0) + 'deg)';

        // Armed state (threshold reached) — flip color, scale bump.
        const nowArmed = dy >= THRESHOLD;
        if (nowArmed !== armed) {
            armed = nowArmed;
            host.classList.toggle('is-armed', armed);
            if (armed) tryHaptic();
        }
    }

    function tryHaptic() {
        try { navigator.vibrate?.(8); } catch (_) {}
        // Capacitor native haptics if available
        try { window.Capacitor?.Plugins?.Haptics?.impact?.({ style: 'LIGHT' }); } catch (_) {}
    }

    function reset() {
        if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
        if (host) {
            host.classList.add('is-resetting');
            host.style.transform = '';
            host.style.opacity = '';
            host.classList.remove('is-armed');
            if (ringEl) ringEl.style.strokeDashoffset = RING_C.toFixed(2);
            if (arrowEl) arrowEl.style.transform = '';
            setTimeout(() => host && host.classList.remove('is-resetting'), 320);
        }
        startY    = null;
        currentDy = 0;
        committed = false;
        armed     = false;
    }

    function triggerRefresh() {
        refreshing = true;
        if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
        if (host) {
            host.classList.add('is-refreshing');
            host.style.transform = 'translate3d(-50%, 24px, 0)';
            host.style.opacity = '1';
        }
        // Brief delay so the spinner state is visible before the reload freezes us
        setTimeout(() => {
            try { window.location.reload(); } catch (_) { location.reload(); }
        }, 320);
    }

    // ── Touch handlers ───────────────────────────────────────
    document.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        if (!canStartPull(e)) return;
        startY = e.touches[0].clientY;
        ensureHost();
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (startY === null) return;
        const dy = e.touches[0].clientY - startY;

        if (dy <= 0) {
            if (committed) { reset(); }
            return;
        }

        if (!committed && dy > ACTIVATION_PX) {
            committed = true;
            document.documentElement.classList.add('is-ptr-ready');
        }

        if (committed) {
            currentDy = dy;
            schedulePaint();
            if (e.cancelable) e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        if (startY === null) return;
        if (committed && currentDy >= THRESHOLD) {
            triggerRefresh();
        } else {
            reset();
        }
        document.documentElement.classList.remove('is-ptr-ready');
    }, { passive: true });

    document.addEventListener('touchcancel', () => {
        reset();
        document.documentElement.classList.remove('is-ptr-ready');
    }, { passive: true });
})();
