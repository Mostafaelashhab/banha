/**
 * Capacitor bridge — wired into the live PWA at banha.shop so that when the
 * page loads inside our Capacitor wrapper (iOS/Android), we upgrade key web
 * features to their native equivalents. On a normal browser visit this is a
 * no-op (the `window.Capacitor` check short-circuits).
 *
 * What we upgrade:
 *   1. Share buttons          → native iOS/Android share sheet (instead of navigator.share)
 *   2. External links         → in-app Browser (no leaving the app)
 *   3. Phone tap tracking     → Haptic feedback + native dialer handoff
 *   4. Push notifications     → APNs/FCM token registration on app start
 *   5. Back button (Android)  → graceful in-app navigation
 *   6. Splash screen          → hide once page is interactive
 *
 * The presence of these native integrations is what differentiates us from a
 * "wrapper" in Apple's App Store review (Guideline 4.2 Minimum Functionality).
 */

(function () {
    if (typeof window === 'undefined' || !window.Capacitor) return;

    const isNative = window.Capacitor.isNativePlatform?.() || false;
    if (!isNative) return; // running in a normal browser — no-op

    document.documentElement.classList.add('is-native-app');
    document.documentElement.dataset.platform = window.Capacitor.getPlatform?.() || 'native';

    // Capacitor plugins are injected by the native shell — pluck them from the global.
    const { Plugins } = window.Capacitor;
    const Share         = Plugins.Share;
    const Browser       = Plugins.Browser;
    const Haptics       = Plugins.Haptics;
    const App           = Plugins.App;
    const SplashScreen  = Plugins.SplashScreen;
    const StatusBar     = Plugins.StatusBar;
    const PushNotifications = Plugins.PushNotifications;

    // ── 1) Share buttons: capture every [data-share] click and route to native ──
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-share]');
        if (!btn || !Share) return;
        e.preventDefault();
        e.stopPropagation();
        const url   = btn.dataset.shareUrl   || location.href;
        const title = btn.dataset.shareTitle || document.title;
        const text  = btn.dataset.shareText  || title;
        Haptics?.impact({ style: 'LIGHT' }).catch(() => {});
        Share.share({ title, text, url, dialogTitle: 'شارك' }).catch(() => {});
    }, true);

    // ── 2) External http(s) links: open in in-app Browser instead of bouncing out
    //    Same-origin (banha.shop) → normal navigation. Other domains → Browser.open()
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a || !Browser) return;
        const href = a.getAttribute('href');
        if (!href) return;

        // Skip if explicitly handled by app or scheme-based
        if (a.hasAttribute('data-native-open')) return;
        if (/^(tel:|sms:|mailto:|whatsapp:|geo:)/i.test(href)) return; // let OS handle
        if (href.startsWith('#') || href.startsWith('/')) return;
        try {
            const u = new URL(href, location.origin);
            if (u.origin === location.origin) return; // same-site → normal nav
            e.preventDefault();
            Browser.open({ url: u.href, presentationStyle: 'popover', toolbarColor: '#FFF7F1' })
                .catch(() => window.open(u.href, '_blank'));
        } catch (_) {}
    }, true);

    // ── 3) Tap haptics on primary action buttons (tiny, ~5ms) ─────────
    document.addEventListener('click', (e) => {
        if (!Haptics) return;
        const el = e.target.closest('.btn-primary, .btn-dark, [data-haptic]');
        if (el) Haptics.impact({ style: 'LIGHT' }).catch(() => {});
    }, true);

    // ── 4) Push notifications: register for APNs (iOS) / FCM (Android) ─
    if (PushNotifications) {
        PushNotifications.requestPermissions().then((p) => {
            if (p.receive !== 'granted') return;
            PushNotifications.register();
        }).catch(() => {});

        PushNotifications.addListener('registration', (token) => {
            // Send to the same backend endpoint used for web push, with a
            // platform hint so the server stores it in the right format.
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrf) return;
                fetch('/push/subscribe-native', {
                    method:  'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        token:    token.value,
                        platform: window.Capacitor.getPlatform(),
                    }),
                }).catch(() => {});
            } catch (_) {}
        });

        PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
            const url = action.notification?.data?.url;
            if (url) location.href = url;
        });
    }

    // ── 5) Android back button: navigate web history first, exit only at root ──
    if (App) {
        App.addListener('backButton', ({ canGoBack }) => {
            if (history.length > 1 && canGoBack) {
                history.back();
            } else {
                App.exitApp();
            }
        });

        // Pause audio / videos when app backgrounds (good iOS citizenship)
        App.addListener('appStateChange', ({ isActive }) => {
            if (!isActive) {
                document.querySelectorAll('video, audio').forEach((m) => m.pause?.());
            }
        });
    }

    // ── 6) Hide splash screen once we're interactive ──
    if (SplashScreen) {
        const hide = () => SplashScreen.hide({ fadeOutDuration: 250 }).catch(() => {});
        if (document.readyState === 'complete') hide();
        else window.addEventListener('load', () => setTimeout(hide, 100));
    }

    // ── 7) iOS-only: configure status bar to overlay the page (matches our CSS) ──
    if (StatusBar) {
        StatusBar.setStyle({ style: 'LIGHT' }).catch(() => {});
        StatusBar.setOverlaysWebView?.({ overlay: true }).catch(() => {});
    }

    console.log('[Capacitor bridge] ready · platform =', window.Capacitor.getPlatform());
})();
