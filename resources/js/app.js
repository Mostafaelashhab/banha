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
        loader?.classList.remove('hidden');

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
    }, { rootMargin: '600px 0px 600px 0px' });

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
    return v && (Date.now() - Number(v)) < 7 * 24 * 60 * 60 * 1000;
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
        // Show iOS sheet after 4s for first-time visitors
        setTimeout(() => {
            if (!document.querySelector('.modal-wrap')) showIOSInstallSheet();
        }, 4000);
    }
    // Android/Desktop: handled via beforeinstallprompt event
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
        alert('فعّل الإشعارات من إعدادات المتصفح علشان تستلم تنبيهات بنهاوي.');
    } else if (result.reason === 'unsupported') {
        alert('متصفحك مش بيدعم Push notifications. استخدم Chrome أو Firefox الأحدث.');
    } else if (result.reason === 'no-vapid') {
        alert('Push notifications مش مفعّلين على السيرفر لسه.');
    }
    btn.disabled = false;
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

