// ─── Guest mode: redirect to login when an action needs auth ─
// `requireAuth()` returns true if the user is logged in; otherwise it
// sends them to /login?redirect=<current url> and returns false so the
// calling handler can bail out.
function requireAuth() {
    const body = document.body;
    if (body?.dataset.guest !== '1') return true;
    const loginUrl = body?.dataset.loginUrl || '/login';
    const back     = window.location.pathname + window.location.search;
    window.location.href = loginUrl + '?redirect=' + encodeURIComponent(back);
    return false;
}
window.requireAuth = requireAuth;

// Catch any auth-required link/button: <a data-needs-auth> / <button data-needs-auth>
document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-needs-auth]');
    if (!el) return;
    if (document.body?.dataset.guest !== '1') return;
    e.preventDefault();
    e.stopPropagation();
    requireAuth();
}, true);

// Catch any auth-required form: <form data-needs-auth>
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-needs-auth]');
    if (!form) return;
    if (document.body?.dataset.guest !== '1') return;
    e.preventDefault();
    e.stopPropagation();
    requireAuth();
}, true);

// ─── Post body "عرض المزيد" (Facebook-style inline expand) ────
function wireExpandable(root = document) {
    root.querySelectorAll('[data-expandable]:not([data-expand-wired])').forEach((wrap) => {
        wrap.dataset.expandWired = '1';
        const p   = wrap.querySelector('p');
        const btn = wrap.querySelector('[data-expand]');
        if (!p || !btn) return;
        // Show button only when text actually overflows the clamp
        if (p.scrollHeight > p.clientHeight + 2) {
            btn.classList.remove('hidden');
        }
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            wrap.classList.add('is-expanded');
            btn.classList.add('hidden');
        });
    });
}
document.addEventListener('DOMContentLoaded', () => wireExpandable());
// Re-run after infinite-scroll appends new posts
document.addEventListener('feed:appended', (e) => wireExpandable(e.target || document));

// ─── Block pinch-zoom (iOS Safari) without breaking scroll ──
// Note: we removed the touchend double-tap blocker because it broke vertical scroll.
// CSS `touch-action: manipulation` already kills double-tap zoom — no JS needed.
document.addEventListener('gesturestart',  (e) => e.preventDefault());
document.addEventListener('gesturechange', (e) => e.preventDefault());
document.addEventListener('gestureend',    (e) => e.preventDefault());

// Block ctrl+wheel zoom on desktop only (passive false required to preventDefault)
document.addEventListener('wheel', (e) => {
    if (e.ctrlKey) e.preventDefault();
}, { passive: false });

// ─── Nav progress bar ───────────────────────────────────────
(function () {
    let bar = document.getElementById('nav-progress');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'nav-progress';
        document.body.prepend(bar);
    }

    const start = () => {
        bar.classList.remove('is-done');
        // restart animation
        bar.classList.remove('is-loading');
        // force reflow
        // eslint-disable-next-line no-unused-expressions
        bar.offsetWidth;
        bar.classList.add('is-loading');
    };

    // Internal link clicks
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        // skip: external, target=_blank, hash-only, javascript:, downloads, modifier keys
        const href = a.getAttribute('href') || '';
        if (
            !href ||
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            href.startsWith('whatsapp:') ||
            href.startsWith('https://wa.me') ||
            a.target === '_blank' ||
            a.hasAttribute('download') ||
            e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ||
            e.button !== 0
        ) return;

        const url = new URL(href, location.origin);
        if (url.origin !== location.origin) return;
        if (url.pathname === location.pathname && url.search === location.search) return;

        start();
    }, true);

    // Form submits
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.dataset.noProgress === '1') return;
        // Skip if not actually submitted (e.g. data-confirm intercepted)
        setTimeout(() => start(), 0);
    });

    // Reset on bfcache restore
    window.addEventListener('pageshow', () => {
        bar.classList.remove('is-loading');
        bar.style.transform = 'scaleX(0)';
    });
})();

// ─── Modal / Sheet system ────────────────────────────────────
const modal = {
    show(html, opts = {}) {
        const wrap = document.createElement('div');
        wrap.className = 'modal-wrap';
        wrap.innerHTML = `
            <div class="modal-backdrop" data-close></div>
            <div class="modal-sheet ${opts.size === 'sm' ? 'modal-sm' : ''}">${html}</div>
        `;
        document.body.appendChild(wrap);
        document.documentElement.classList.add('overflow-hidden');
        requestAnimationFrame(() => wrap.classList.add('open'));

        const onKey = (e) => { if (e.key === 'Escape') modal.hide(wrap); };
        document.addEventListener('keydown', onKey);
        wrap._cleanup = () => document.removeEventListener('keydown', onKey);

        wrap.addEventListener('click', (e) => {
            if (e.target.matches('[data-close]')) modal.hide(wrap);
        });

        return wrap;
    },

    hide(wrap) {
        if (!wrap) return;
        wrap.classList.remove('open');
        wrap._cleanup?.();
        document.documentElement.classList.remove('overflow-hidden');
        setTimeout(() => wrap.remove(), 220);
    },

    confirm({ title, body = '', action = 'تأكيد', cancel = 'إلغاء', tone = 'primary' }) {
        return new Promise((resolve) => {
            const html = `
                <div class="p-5">
                    <h3 class="text-lg font-extrabold text-ink-950 mb-1">${title}</h3>
                    ${body ? `<p class="text-ink-500 text-sm leading-relaxed">${body}</p>` : ''}
                    <div class="flex gap-2 mt-5">
                        <button type="button" class="btn-ghost flex-1 justify-center" data-cancel>${cancel}</button>
                        <button type="button" class="${tone === 'danger' ? 'btn-dark' : 'btn-primary'} flex-1 justify-center" data-ok>${action}</button>
                    </div>
                </div>`;
            const w = modal.show(html, { size: 'sm' });
            w.querySelector('[data-ok]').onclick = () => { modal.hide(w); resolve(true); };
            w.querySelector('[data-cancel]').onclick = () => { modal.hide(w); resolve(false); };
            w.addEventListener('click', (e) => {
                if (e.target.matches('.modal-backdrop')) resolve(false);
            });
        });
    },
};

window.banhawyModal = modal;

// ─── Auto-handle <form data-confirm="..."> ───────────────────
document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-confirm]');
    if (!form || form.dataset.confirmed === '1') return;

    e.preventDefault();
    const ok = await modal.confirm({
        title:  form.dataset.confirm,
        body:   form.dataset.confirmBody || '',
        action: form.dataset.confirmAction || 'تأكيد',
        tone:   form.dataset.confirmTone || 'primary',
    });
    if (ok) {
        form.dataset.confirmed = '1';
        form.submit();
    }
}, true);

// ─── Report sheet ────────────────────────────────────────────
const REPORT_REASONS = [
    ['spam',  'سبام / إعلانات',     'بوست متكرر أو إعلاني'],
    ['abuse', 'إساءة / تنمر',        'شتيمة، تهديد، أو تنمر على حد'],
    ['nsfw',  'محتوى للكبار',        'صور/كلام جنسي أو عنف'],
    ['fake',  'خبر مزيف / إشاعة',    'معلومة كاذبة بتنشر هلع'],
    ['other', 'حاجة تانية',          'سبب مش موجود فوق'],
];

document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-report]');
    if (!btn) return;

    e.preventDefault();
    const url = btn.dataset.report;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!url || !csrf) return;

    const reasonsHtml = REPORT_REASONS.map(([key, label, desc]) => `
        <label class="flex items-start gap-3 p-3.5 rounded-2xl bg-cream-100 border border-ink-950/8 cursor-pointer has-[:checked]:bg-coral-100 has-[:checked]:border-coral-500/40 transition mb-2">
            <input type="radio" name="reason" value="${key}" class="peer mt-1 accent-coral-500" ${key === 'spam' ? 'checked' : ''}>
            <span class="flex-1">
                <span class="block font-bold text-ink-950 text-sm">${label}</span>
                <span class="block text-xs text-ink-500 mt-0.5">${desc}</span>
            </span>
        </label>`).join('');

    const html = `
        <form method="POST" action="${url}" class="p-5">
            <input type="hidden" name="_token" value="${csrf}">
            <h3 class="text-lg font-extrabold text-ink-950 mb-1">بلّغ عن البوست</h3>
            <p class="text-ink-500 text-sm mb-4">اختار السبب — التقارير اللي بتترفع كذب بتأثر على سمعتك.</p>

            <div class="max-h-[60vh] overflow-y-auto -mx-1 px-1">${reasonsHtml}</div>

            <textarea name="details" maxlength="500" rows="2" placeholder="تفاصيل إضافية (اختياري)…"
                class="mt-2 w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none text-sm"></textarea>

            <div class="flex gap-2 mt-4">
                <button type="button" class="btn-ghost flex-1 justify-center" data-close>إلغاء</button>
                <button type="submit" class="btn-primary flex-1 justify-center">أرسل البلاغ</button>
            </div>
        </form>`;

    modal.show(html);
});

// ─── Auto-hide flash ─────────────────────────────────────────
setTimeout(() => document.querySelectorAll('[data-flash]').forEach(el => el.remove()), 4500);

// ─── Infinite scroll for the feed ───────────────────────────
(function () {
    const list = document.querySelector('[data-infinite-scroll]');
    if (!list || !('IntersectionObserver' in window)) return;

    const loader = document.querySelector('[data-feed-loader]');
    const done   = document.querySelector('[data-feed-done]');
    let loading  = false;
    let nextUrl  = list.querySelector('[data-feed-end]')?.dataset.nextUrl || '';
    let hasMore  = list.querySelector('[data-feed-end]')?.dataset.hasMore === '1';

    if (!hasMore) { done?.classList.remove('hidden'); return; }

    const fetchNext = async () => {
        if (loading || !hasMore || !nextUrl) return;
        loading = true;
        // No spinner — feel instant (next page is prefetched well before user reaches it)

        try {
            const url = new URL(nextUrl, location.origin);
            url.searchParams.set('partial', '1');
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('Bad response');
            const html = await res.text();

            // Remove old sentinel
            list.querySelector('[data-feed-end]')?.remove();

            // Append new HTML
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            while (tmp.firstChild) list.appendChild(tmp.firstChild);

            // Re-wire any expandable post bodies that just appeared
            list.dispatchEvent(new CustomEvent('feed:appended', { bubbles: true }));

            // Update next URL from the new sentinel
            const sentinel = list.querySelector('[data-feed-end]');
            nextUrl = sentinel?.dataset.nextUrl || '';
            hasMore = sentinel?.dataset.hasMore === '1';

            if (!hasMore) {
                done?.classList.remove('hidden');
                io.disconnect();
            } else {
                io.observe(sentinel);
            }
        } catch (err) {
            // soft-fail: stop trying
            io.disconnect();
            done?.classList.remove('hidden');
        } finally {
            loading = false;
            loader?.classList.add('hidden');
        }
    };

    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) fetchNext();
        }
    }, { rootMargin: '1500px 0px 1500px 0px' });

    const sentinel = list.querySelector('[data-feed-end]');
    if (sentinel) io.observe(sentinel);
})();

// ─── Reveal-on-scroll ────────────────────────────────────────
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
}

// ─── PWA: register service worker ───────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

// ─── Install prompt (Android/Desktop) + iOS sheet ────────────
const isIOS    = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
const dismissedKey = 'banhawy_install_dismissed_at';

let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallBanner();
});

function recentlyDismissed() {
    const v = localStorage.getItem(dismissedKey);
    // Re-prompt every 3 days (was 7 — too lenient for first-visit nudge)
    return v && (Date.now() - Number(v)) < 3 * 24 * 60 * 60 * 1000;
}

function dismissInstall() {
    localStorage.setItem(dismissedKey, String(Date.now()));
    document.getElementById('install-banner')?.remove();
}

function showInstallBanner() {
    if (isStandalone || recentlyDismissed() || document.getElementById('install-banner')) return;

    const el = document.createElement('div');
    el.id = 'install-banner';
    el.className = 'install-banner';
    el.innerHTML = `
        <div class="install-icon">
            <img src="/icons/icon-192.png" width="44" height="44" alt="بنهاوي">
        </div>
        <div class="install-text">
            <div class="install-title">حمّل بنهاوي</div>
            <div class="install-sub">افتحه في ثانية من الـ home screen</div>
        </div>
        <button class="install-cta" data-action="install">حمّل</button>
        <button class="install-close" data-action="dismiss" aria-label="إغلاق">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `;
    document.body.appendChild(el);

    el.addEventListener('click', async (e) => {
        const t = e.target.closest('[data-action]');
        if (!t) return;
        if (t.dataset.action === 'install') {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
            }
            dismissInstall();
        } else if (t.dataset.action === 'dismiss') {
            dismissInstall();
        }
    });
}

function showIOSInstallSheet() {
    if (recentlyDismissed()) return;
    const html = `
        <div class="p-5">
            <h3 class="text-lg font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <img src="/icons/icon-192.png" width="32" height="32" alt="" class="rounded-lg">
                نزّل بنهاوي على iPhone
            </h3>
            <p class="text-ink-500 text-sm mb-5 leading-relaxed">عشان تفتحه زي أي تطبيق من الشاشة الرئيسية، اعمل الخطوات دي:</p>
            <ol class="space-y-3 text-sm text-ink-950">
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">1</span>
                    <span>اضغط على زر <b>المشاركة</b> في الـ Safari (المربّع بسهم لفوق <span class="text-coral-600 font-bold">⬆️</span>) من شريط الأدوات.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">2</span>
                    <span>اعمل scroll لتحت واختار <b>"Add to Home Screen"</b> (أضف إلى الشاشة الرئيسية).</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">3</span>
                    <span>اضغط <b>إضافة</b>، وهتلاقي بنهاوي بأيقونته على الـ home screen 🎉</span>
                </li>
            </ol>
            <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-3 mt-5">
                <p class="text-xs text-ink-500">
                    <b class="text-ink-950">ملاحظة:</b>
                    لازم تستخدم <b>Safari</b> — الـ Chrome على iOS مش بيدعم Add to Home Screen.
                </p>
            </div>
            <div class="flex gap-2 mt-5">
                <button type="button" class="btn-ghost flex-1 justify-center" data-close>تمام</button>
            </div>
        </div>`;
    if (window.banhawyModal) {
        window.banhawyModal.show(html);
    }
}

// Trigger banners on landing only after a slight delay
function maybeShowInstallPrompt() {
    if (isStandalone) return;
    if (recentlyDismissed()) return;

    if (isIOS) {
        // iOS doesn't fire beforeinstallprompt — show our manual sheet ASAP.
        // Tiny delay so the page paints first; not the long 2s nudge.
        setTimeout(() => {
            if (!document.querySelector('.modal-wrap.open')) showIOSInstallSheet();
        }, 400);
        return;
    }

    // Android/Desktop: Chrome fires beforeinstallprompt only after the user
    // has engaged with the page (a few seconds of interaction). If that fires,
    // showInstallBanner() runs from the listener above. As a fallback for
    // browsers that never fire it (Firefox, some Samsung builds, locked PWA)
    // show a passive install hint after 8s — only if no native banner showed.
    setTimeout(() => {
        if (deferredPrompt) return;                                  // native path took over
        if (document.getElementById('install-banner')) return;       // already shown
        if (recentlyDismissed()) return;
        showAndroidFallbackBanner();
    }, 8000);
}

function showAndroidFallbackBanner() {
    if (document.getElementById('install-banner')) return;
    const ua = navigator.userAgent;
    const isFirefox = /Firefox|FxiOS/i.test(ua);
    const isChromium = /Chrome|CriOS|Edg|SamsungBrowser/i.test(ua) && !isFirefox;
    // Only show fallback when there's no native path; Chromium without an event
    // means heuristic not met — still useful to surface the option.
    const el = document.createElement('div');
    el.id = 'install-banner';
    el.className = 'install-banner';
    const hint = isFirefox
        ? 'افتح القايمة (⋮) واختار "تثبيت" أو "Add to Home screen"'
        : 'افتح قايمة Chrome (⋮) واختار "Add to Home screen"';
    el.innerHTML = `
        <div class="install-icon">
            <img src="/icons/icon-192.png" width="44" height="44" alt="بنهاوي">
        </div>
        <div class="install-text">
            <div class="install-title">حمّل بنهاوي</div>
            <div class="install-sub">${hint}</div>
        </div>
        <button class="install-close" data-action="dismiss" aria-label="إغلاق">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `;
    document.body.appendChild(el);
    el.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="dismiss"]')) dismissInstall();
    });
}

// expose for manual trigger (e.g., from a "حمّل التطبيق" button)
window.banhawyInstall = {
    showAndroid: () => deferredPrompt?.prompt(),
    showIOS: showIOSInstallSheet,
    maybeShow: maybeShowInstallPrompt,
};

if (document.body.dataset.installPrompt === 'auto') {
    maybeShowInstallPrompt();
}

// ─── Push notifications ────────────────────────────────────
const urlBase64ToUint8Array = (b64) => {
    const padding = '='.repeat((4 - (b64.length % 4)) % 4);
    const raw = atob((b64 + padding).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from(raw, (c) => c.charCodeAt(0));
};

async function pushSubscribe() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return { ok: false, reason: 'unsupported' };
    }
    try {
        const reg = await navigator.serviceWorker.ready;
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') return { ok: false, reason: 'denied' };

        const r = await fetch('/push/vapid');
        const { key } = await r.json();
        if (!key) return { ok: false, reason: 'no-vapid' };

        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(key),
        });

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        await fetch('/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(sub),
        });
        return { ok: true };
    } catch (e) {
        return { ok: false, reason: 'error', error: String(e) };
    }
}

async function pushUnsubscribe() {
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (!sub) return { ok: true };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        await fetch('/push/unsubscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ endpoint: sub.endpoint }),
        });
        await sub.unsubscribe();
        return { ok: true };
    } catch (e) {
        return { ok: false };
    }
}

window.banhawyPush = { subscribe: pushSubscribe, unsubscribe: pushUnsubscribe };

// ─── First-visit notification permission prompt ──────────────
(function () {
    const KEY = 'banhawy_notif_prompt_at';

    const shouldAsk = () => {
        if (!('serviceWorker' in navigator) || !('Notification' in window) || !('PushManager' in window)) return false;
        if (Notification.permission !== 'default') return false;
        if (!document.querySelector('meta[name="csrf-token"]')) return false;
        if (!document.querySelector('.bottom-nav')) return false;
        // Don't fight with PWA install banner / other modal
        if (document.getElementById('install-banner')) return false;
        if (document.querySelector('.modal-wrap.open')) return false;
        const last = localStorage.getItem(KEY);
        // Re-ask after 3 days if dismissed (was 7 — too lenient)
        if (last && (Date.now() - Number(last)) < 3 * 24 * 60 * 60 * 1000) return false;
        return true;
    };

    const askNow = () => {
        if (!window.banhawyModal) return;
        const html = `
            <div class="p-5 text-center">
                <div class="w-16 h-16 rounded-2xl brand-bg grid place-items-center mx-auto mb-4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-ink-950 mb-2">نوصّلك أهم اللي بيحصل في بنها؟</h3>
                <p class="text-ink-500 text-sm leading-relaxed mb-5">
                    تنبيهات لحظية عن الزحمة والكهربا في حيك،
                    <br>وإشعارات لو حد عمل like أو علّق على بوستك.
                </p>
                <div class="flex gap-2">
                    <button type="button" class="btn-ghost flex-1 justify-center" data-notif-skip>مش دلوقتي</button>
                    <button type="button" class="btn-primary flex-1 justify-center" data-notif-allow>اسمحلي</button>
                </div>
                <p class="text-[10px] text-ink-400 mt-3">تقدر تطفّيها أي وقت من إعدادات حسابك</p>
            </div>`;
        const wrap = window.banhawyModal.show(html);

        wrap.querySelector('[data-notif-allow]').onclick = async () => {
            window.banhawyModal.hide(wrap);
            const r = await pushSubscribe();
            localStorage.setItem(KEY, String(Date.now()));
            if (r.ok) {
                showShareToast('✓ التنبيهات اتفعّلت');
            } else if (r.reason === 'denied') {
                showShareToast('فعّلها من إعدادات المتصفح وقتما تحب');
            }
        };
        wrap.querySelector('[data-notif-skip]').onclick = () => {
            window.banhawyModal.hide(wrap);
            localStorage.setItem(KEY, String(Date.now()));
        };
    };

    // Try after 3.5s. If install banner is up, re-poll every 3s for up to 30s
    let tries = 0;
    const tryShow = () => {
        if (shouldAsk()) { askNow(); return; }
        if (++tries < 10 && Notification.permission === 'default') setTimeout(tryShow, 3000);
    };
    setTimeout(tryShow, 3500);
})();

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-push-toggle]');
    if (!btn) return;
    e.preventDefault();
    btn.disabled = true;
    const wasOn = btn.dataset.pushOn === '1';
    const result = wasOn ? await pushUnsubscribe() : await pushSubscribe();
    if (result.ok) {
        btn.dataset.pushOn = wasOn ? '0' : '1';
        btn.textContent = wasOn ? 'تشغيل التنبيهات' : 'تنبيهات شغّالة ✓';
    } else if (result.reason === 'denied') {
        showShareToast('فعّل الإشعارات من إعدادات المتصفح');
    } else if (result.reason === 'unsupported') {
        showShareToast('المتصفح مش بيدعم الإشعارات');
    } else if (result.reason === 'no-vapid') {
        showShareToast('الإشعارات مش مفعّلة على السيرفر');
    }
    btn.disabled = false;
});

// ─── Feed filter sheet ──────────────────────────────────────
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-feed-filter]');
    if (!btn) return;
    e.preventDefault();
    const tpl = document.getElementById('feed-filter-template');
    if (!tpl || !window.banhawyModal) return;
    window.banhawyModal.show(tpl.innerHTML);
});

// ─── Real-time voting (AJAX, optimistic update) ──────────────
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-vote]');
    if (!btn) return;
    e.preventDefault();
    if (!requireAuth()) return;

    const block = btn.closest('[data-vote-block]');
    if (!block || block.dataset.voting === '1') return;
    block.dataset.voting = '1';

    const url        = block.dataset.voteUrl;
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content;
    const value      = parseInt(btn.dataset.vote, 10); // 1 or -1
    const currentVal = parseInt(block.dataset.myVote || '0', 10);
    const newValue   = currentVal === value ? 0 : value;

    const upEl   = block.querySelector('[data-count="up"]');
    const downEl = block.querySelector('[data-count="down"]');
    const upBtn  = block.querySelector('[data-vote="1"]');
    const downBtn= block.querySelector('[data-vote="-1"]');

    const prevUp   = parseInt(upEl?.textContent || '0', 10);
    const prevDown = parseInt(downEl?.textContent || '0', 10);

    // Optimistic delta calculation
    let upDelta   = (newValue === 1 ? 1 : 0) - (currentVal === 1 ? 1 : 0);
    let downDelta = (newValue === -1 ? 1 : 0) - (currentVal === -1 ? 1 : 0);

    upEl   && (upEl.textContent   = Math.max(0, prevUp + upDelta));
    downEl && (downEl.textContent = Math.max(0, prevDown + downDelta));

    // Toggle visual states
    upBtn?.classList.toggle('is-liked',     newValue === 1);
    downBtn?.classList.toggle('is-disliked', newValue === -1);
    block.dataset.myVote = newValue;
    btn.classList.add('pop');
    setTimeout(() => btn.classList.remove('pop'), 400);

    // Toggle filled svg (rerender icon — find <svg> inside btn and flip fill)
    const flipFill = (b, filled) => {
        const svg = b?.querySelector('svg');
        if (!svg) return;
        if (filled) {
            svg.setAttribute('fill', 'currentColor');
            svg.removeAttribute('stroke');
        } else {
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
        }
    };
    flipFill(upBtn,   newValue === 1);
    flipFill(downBtn, newValue === -1);

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'value=' + encodeURIComponent(String(newValue)),
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Bad response');
        const data = await res.json();
        if (typeof data.upvotes === 'number')   upEl   && (upEl.textContent   = data.upvotes);
        if (typeof data.downvotes === 'number') downEl && (downEl.textContent = data.downvotes);
    } catch (err) {
        // Rollback
        upEl   && (upEl.textContent   = prevUp);
        downEl && (downEl.textContent = prevDown);
        upBtn?.classList.toggle('is-liked',     currentVal === 1);
        downBtn?.classList.toggle('is-disliked', currentVal === -1);
        flipFill(upBtn,   currentVal === 1);
        flipFill(downBtn, currentVal === -1);
        block.dataset.myVote = currentVal;
    } finally {
        delete block.dataset.voting;
    }
});

// ─── Share button (Web Share API + clipboard fallback) ─────
function showShareToast(text) {
    const t = document.createElement('div');
    t.className = 'banhawy-toast';
    t.textContent = text;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('in'));
    setTimeout(() => {
        t.classList.remove('in');
        setTimeout(() => t.remove(), 300);
    }, 2200);
}

// ─── Client-side image compression (prevents PHP post_max_size silent fails) ──
// Compresses any picked image file to < 1MB before upload, so even 4K phone photos work.
async function compressImageFile(file) {
    if (!file || !file.type || !file.type.startsWith('image/')) return file;
    if (file.size < 800 * 1024) return file; // already small enough

    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(url);
            const maxW = 1280;
            const ratio = Math.min(1, maxW / img.naturalWidth);
            const w = Math.round(img.naturalWidth * ratio);
            const h = Math.round(img.naturalHeight * ratio);
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            canvas.toBlob((blob) => {
                if (!blob) { resolve(file); return; }
                const newFile = new File([blob], (file.name || 'photo').replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                resolve(newFile.size < file.size ? newFile : file);
            }, 'image/jpeg', 0.78);
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

document.addEventListener('change', async (e) => {
    const input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
    const accept = (input.accept || '').toLowerCase();
    if (!accept.includes('image')) return;
    if (!input.files || !input.files[0]) return;
    if (input.dataset.compressed === '1') { input.dataset.compressed = ''; return; }

    const original = input.files[0];
    if (original.size > 12 * 1024 * 1024) {
        if (typeof showShareToast === 'function') {
            showShareToast('الصورة كبيرة قوي — جرّب صورة أصغر من ١٢ ميجا');
        }
        input.value = '';
        return;
    }

    try {
        const compressed = await compressImageFile(original);
        if (compressed !== original) {
            const dt = new DataTransfer();
            dt.items.add(compressed);
            input.dataset.compressed = '1';
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    } catch (err) { /* let server handle it */ }
}, true);

// ─── Business contact-click tracking (sendBeacon, doesn't block navigation) ──
document.addEventListener('click', (e) => {
    const a = e.target.closest('[data-track-click][data-business]');
    if (!a) return;
    const url = '/directory/business/' + encodeURIComponent(a.dataset.business) + '/click?kind=' + encodeURIComponent(a.dataset.trackClick);
    if (navigator.sendBeacon) {
        navigator.sendBeacon(url);
    } else {
        fetch(url, { method: 'GET', keepalive: true, credentials: 'same-origin' });
    }
});

// ─── Bookmark toggle (AJAX) ──────────────────────────────────
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-bookmark]');
    if (!btn) return;
    e.preventDefault();
    if (!requireAuth()) return;
    if (btn.dataset.busy === '1') return;
    btn.dataset.busy = '1';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const wasSaved = btn.dataset.saved === '1';
    btn.dataset.saved = wasSaved ? '0' : '1';
    btn.classList.toggle('text-coral-500', !wasSaved);

    const svg = btn.querySelector('svg');
    if (svg) {
        if (!wasSaved) { svg.setAttribute('fill', 'currentColor'); svg.removeAttribute('stroke'); }
        else { svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); }
    }

    try {
        const res = await fetch('/bookmark', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: 'type=' + encodeURIComponent(btn.dataset.type) + '&id=' + encodeURIComponent(btn.dataset.id),
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('bookmark failed');
    } catch (err) {
        // rollback
        btn.dataset.saved = wasSaved ? '1' : '0';
        btn.classList.toggle('text-coral-500', wasSaved);
        if (svg) {
            if (wasSaved) { svg.setAttribute('fill', 'currentColor'); svg.removeAttribute('stroke'); }
            else { svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); }
        }
    } finally {
        delete btn.dataset.busy;
    }
});

// ─── Sub-type picker: toggle "other" custom field ────────────
document.addEventListener('change', (e) => {
    const radio = e.target.closest('[data-subtype-picker] input[type="radio"][name="sub_type"]');
    if (!radio) return;
    const wrap   = radio.closest('[data-subtype-picker]');
    const custom = wrap?.querySelector('[data-custom-subtype-wrap]');
    if (!custom) return;
    const isOther = radio.dataset.isOther === '1';
    custom.classList.toggle('hidden', !isOther);
    if (isOther) custom.querySelector('input')?.focus();
});

// ─── Comment likes (AJAX, optimistic) ────────────────────────
document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-comment-like]');
    if (!form) return;
    e.preventDefault();
    if (!requireAuth()) return;

    const btn      = form.querySelector('button[type="submit"]');
    if (!btn || btn.dataset.busy === '1') return;
    btn.dataset.busy = '1';

    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content;
    const countEl  = btn.querySelector('[data-like-count]');
    const wasLiked = btn.dataset.liked === '1';
    const newLiked = !wasLiked;
    const prev     = parseInt(countEl?.textContent || '0', 10);

    // Optimistic UI
    btn.dataset.liked = newLiked ? '1' : '0';
    btn.classList.toggle('bg-coral-500', newLiked);
    btn.classList.toggle('text-white', newLiked);
    btn.classList.toggle('text-ink-500', !newLiked);
    btn.classList.toggle('hover:bg-cream-100', !newLiked);
    if (countEl) countEl.textContent = Math.max(0, prev + (newLiked ? 1 : -1));
    const svg = btn.querySelector('svg');
    if (svg) {
        if (newLiked) { svg.setAttribute('fill', 'currentColor'); svg.removeAttribute('stroke'); }
        else { svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); }
    }
    btn.classList.add('pop');
    setTimeout(() => btn.classList.remove('pop'), 400);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('like failed');
        const data = await res.json();
        if (typeof data.upvotes === 'number' && countEl) countEl.textContent = data.upvotes;
    } catch (err) {
        // rollback
        btn.dataset.liked = wasLiked ? '1' : '0';
        btn.classList.toggle('bg-coral-500', wasLiked);
        btn.classList.toggle('text-white', wasLiked);
        btn.classList.toggle('text-ink-500', !wasLiked);
        btn.classList.toggle('hover:bg-cream-100', !wasLiked);
        if (countEl) countEl.textContent = prev;
        if (svg) {
            if (wasLiked) { svg.setAttribute('fill', 'currentColor'); svg.removeAttribute('stroke'); }
            else { svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); }
        }
    } finally {
        delete btn.dataset.busy;
    }
});

// ─── Reply form toggle ──────────────────────────────────────
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-reply-toggle]');
    if (!trigger) return;
    const id = trigger.dataset.replyToggle;
    const form = document.querySelector(`form[data-reply-form="${id}"]`);
    if (!form) return;
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        form.querySelector('textarea')?.focus();
    }
});

// ─── Three-dot overflow menus ───────────────────────────────
document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-menu-toggle]');
    // Close all panels first
    document.querySelectorAll('[data-menu-panel]').forEach(p => {
        if (toggle && p === toggle.parentElement.querySelector('[data-menu-panel]')) return;
        p.classList.add('hidden');
    });
    if (toggle) {
        const panel = toggle.parentElement.querySelector('[data-menu-panel]');
        panel?.classList.toggle('hidden');
    }
});

// ─── Comment report (modal) ─────────────────────────────────
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-comment-report]');
    if (!trigger) return;
    e.preventDefault();
    const action = trigger.dataset.action;
    const csrf   = trigger.dataset.csrf;

    const reasons = [
        ['spam',  'سبام / إعلان'],
        ['abuse', 'إهانة / تنمر'],
        ['nsfw',  'محتوى مسيء'],
        ['fake',  'كذب / تضليل'],
        ['other', 'حاجة تانية'],
    ];

    const wrap = document.createElement('div');
    wrap.className = 'modal-wrap';
    wrap.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-sheet">
            <div class="p-5">
                <h3 class="text-lg font-extrabold text-ink-950 mb-4">ابلاغ عن الكومنت</h3>
                <form method="POST" action="${action}" class="space-y-2">
                    <input type="hidden" name="_token" value="${csrf}">
                    ${reasons.map(([v, l]) => `
                        <label class="flex items-center gap-2 p-3 rounded-2xl bg-cream-100 border border-ink-950/8 cursor-pointer has-[:checked]:bg-coral-500 has-[:checked]:text-white has-[:checked]:border-coral-500 transition">
                            <input type="radio" name="reason" value="${v}" required class="accent-coral-500">
                            <span class="font-bold text-sm">${l}</span>
                        </label>
                    `).join('')}
                    <div class="flex gap-2 pt-2">
                        <button type="button" class="btn-ghost flex-1 justify-center" data-close>إلغاء</button>
                        <button type="submit" class="btn-primary flex-1 justify-center">ابعت البلاغ</button>
                    </div>
                </form>
            </div>
        </div>`;
    document.body.appendChild(wrap);
    requestAnimationFrame(() => wrap.classList.add('open'));
    const close = () => { wrap.classList.remove('open'); setTimeout(() => wrap.remove(), 220); };
    wrap.querySelector('.modal-backdrop').addEventListener('click', close);
    wrap.querySelector('[data-close]').addEventListener('click', close);
});

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-share]');
    if (!btn) return;
    e.preventDefault();

    const url   = new URL(btn.dataset.shareUrl || location.href, location.origin).href;
    const title = btn.dataset.shareTitle || 'بنهاوي';
    const text  = btn.dataset.shareText  || '';
    const composed = `${text ? text + '\n\n' : ''}${url}\n\nمن بنهاوي 🔥`;

    if (navigator.share) {
        try {
            await navigator.share({ title, text: composed, url });
            return;
        } catch (err) {
            // user cancelled — silent
            if (err.name === 'AbortError') return;
        }
    }

    // Fallback: copy
    try {
        await navigator.clipboard.writeText(composed);
        showShareToast('✓ اللينك اتنسخ — جاهز للشير');
    } catch (err) {
        prompt('انسخ اللينك:', url);
    }
});

