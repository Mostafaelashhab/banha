/**
 * Pull-to-refresh — native-style downward pull from the top of the page to
 * reload. Renders a circular spinner that fades and rotates as the user pulls
 * past the threshold, then reloads when released.
 *
 * Constraints:
 *   • Only triggers when the document is scrolled to the very top.
 *   • Skipped when the touch starts inside a horizontally-scrollable element,
 *     an input/textarea, the Leaflet map, or anything tagged [data-no-ptr].
 *   • Disabled on desktop (no touch input).
 *
 * No dependency. Inserts its own DOM at runtime — no Blade changes needed.
 */
(function () {
    if (typeof window === 'undefined') return;
    // Skip on devices that aren't touch-primary
    if (!matchMedia('(hover: none) and (pointer: coarse)').matches) return;
    // Skip when document already explicitly disables PTR
    if (document.documentElement.dataset.ptr === 'off') return;

    const THRESHOLD     = 80;   // px past which release will trigger reload
    const MAX_PULL      = 140;  // px hard cap on visual stretch
    const ACTIVATION_PX = 8;    // px before we commit (vs treating as a tap)
    const STAGE_CLASS   = 'is-ptr-ready';

    // ── DOM: a small floating spinner host inserted once on first pull ──
    let host = null;
    function ensureHost() {
        if (host) return host;
        host = document.createElement('div');
        host.className = 'ptr-host';
        host.setAttribute('aria-hidden', 'true');
        host.innerHTML =
            '<div class="ptr-pill">' +
                '<svg class="ptr-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">' +
                    '<path d="M21 12a9 9 0 1 1-3-6.7" />' +
                    '<polyline points="21 4 21 10 15 10"/>' +
                '</svg>' +
            '</div>';
        document.body.appendChild(host);
        return host;
    }

    // ── State ──────────────────────────────────────────────────
    let startY      = null;
    let lastY       = null;
    let pullPx      = 0;
    let committed   = false;
    let refreshing  = false;
    let starterEl   = null;

    /** Should this touch start a pull? */
    function canStartPull(e) {
        if (refreshing) return false;
        if (window.scrollY > 0 || document.documentElement.scrollTop > 0) return false;
        const t = e.target;
        if (!t || !t.closest) return false;
        if (t.closest('input, textarea, select, [contenteditable="true"]')) return false;
        if (t.closest('[data-no-ptr]')) return false;
        if (t.closest('#banha-map, .leaflet-container')) return false;
        // Sheets/modals open → they have their own scrolling
        if (document.body.classList.contains('lb-locked')) return false;
        if (document.body.style.overflow === 'hidden') return false;
        // If we're inside a scrollable element that's been scrolled — let it handle the gesture
        let n = t;
        while (n && n !== document.body) {
            const cs = getComputedStyle(n);
            if ((cs.overflowY === 'auto' || cs.overflowY === 'scroll' || cs.overflowX === 'auto' || cs.overflowX === 'scroll') && (n.scrollTop > 0 || n.scrollLeft > 0)) return false;
            n = n.parentElement;
        }
        return true;
    }

    function setPullVisual(dy) {
        ensureHost();
        // Decay past the threshold so it feels resistant
        const eased = dy > THRESHOLD
            ? THRESHOLD + (Math.min(dy, MAX_PULL) - THRESHOLD) * 0.4
            : dy;
        host.style.transform = 'translate(-50%, ' + (eased - 32) + 'px)';
        host.style.opacity   = String(Math.min(1, dy / 50));
        host.classList.toggle('is-armed', dy >= THRESHOLD);
        const rot = Math.min(360, (dy / THRESHOLD) * 360);
        const spinner = host.querySelector('.ptr-spinner');
        if (spinner) spinner.style.transform = 'rotate(' + rot + 'deg)';
    }

    function reset(animate) {
        if (host) {
            if (animate) host.classList.add('is-animating');
            host.style.transform = '';
            host.style.opacity = '';
            host.classList.remove('is-armed');
            setTimeout(() => host && host.classList.remove('is-animating'), 240);
        }
        startY = lastY = null;
        pullPx = 0;
        committed = false;
        starterEl = null;
        document.documentElement.classList.remove(STAGE_CLASS);
    }

    function triggerRefresh() {
        refreshing = true;
        if (host) {
            host.classList.add('is-refreshing');
            host.style.transform = 'translate(-50%, 28px)';
            host.style.opacity = '1';
        }
        // Give the spinner ~250ms to render before the navigation freezes the page
        setTimeout(() => {
            try { window.location.reload(); } catch (_) { location.reload(); }
        }, 280);
    }

    // ── Event handlers ─────────────────────────────────────────
    document.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        if (!canStartPull(e)) return;
        startY = e.touches[0].clientY;
        lastY  = startY;
        starterEl = e.target;
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (startY === null) return;
        const y  = e.touches[0].clientY;
        const dy = y - startY;
        lastY = y;

        if (dy <= 0) {
            if (committed) reset(false);
            return;
        }

        if (!committed && dy > ACTIVATION_PX) {
            committed = true;
            document.documentElement.classList.add(STAGE_CLASS);
        }

        if (committed) {
            pullPx = dy;
            setPullVisual(dy);
            // Block native scroll for the duration of the pull
            if (e.cancelable) e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        if (startY === null) return;
        if (committed && pullPx >= THRESHOLD) {
            triggerRefresh();
        } else {
            reset(true);
        }
    }, { passive: true });

    document.addEventListener('touchcancel', () => reset(true), { passive: true });
})();
