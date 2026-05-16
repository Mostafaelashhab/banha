{{-- Shared invite-preview modal — used by both admin/businesses (list) and
     directory/show (single business view in admin mode).

     Wire-up contract:
       - Any element with `data-invite-open` triggers the modal.
       - It must carry these attributes:
            data-invite-url="{{ route('admin.businesses.invite.preview', $b) }}"
            data-invite-send-url="{{ route('admin.businesses.invite.send', $b) }}"
       - The JS at the bottom of this file finds them, hydrates the modal
         from the preview JSON, and submits the form to the send URL.

     Only included once per page (guard with @once). --}}
@once
<div data-invite-modal class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-invite-backdrop></div>
    <div class="absolute inset-0 grid place-items-center p-4">
        <div class="bg-white text-ink-950 rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
            <div class="px-5 pt-5 pb-3 border-b border-ink-950/8">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-mint-100 text-mint-700 grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-black">دعوة استلام الصفحة</h3>
                        <p class="text-[11px] text-ink-500 mt-0.5">
                            هتتبعت لـ <span data-invite-name class="font-bold text-ink-950"></span>
                            على <span data-invite-phone dir="ltr" class="font-mono font-bold text-ink-950"></span>
                        </p>
                    </div>
                    <button type="button" data-invite-close
                            class="w-8 h-8 rounded-full bg-cream-200 text-ink-500 grid place-items-center hover:bg-cream-300 transition shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                {{-- "Already sent" indicator was here — removed because re-sending
                     is now allowed without any visual friction. --}}
            </div>

            <div class="px-5 py-4 overflow-y-auto flex-1">
                <div class="text-[10px] font-bold text-ink-500 mb-2">معاينة الرسالة</div>
                <pre data-invite-body class="bg-cream-50 ring-1 ring-ink-950/8 rounded-2xl p-4 text-[12px] leading-relaxed whitespace-pre-wrap font-sans text-ink-950"></pre>
            </div>

            <form method="POST" data-invite-form class="px-5 py-3 border-t border-ink-950/8 flex items-center gap-2">
                @csrf
                <button type="button" data-invite-close
                        class="flex-1 px-4 py-2.5 rounded-full bg-cream-200 text-ink-700 text-sm font-extrabold hover:bg-cream-300 transition">
                    إلغاء
                </button>
                <button type="submit" data-invite-submit
                        class="flex-[2] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full bg-coral-500 text-white text-sm font-extrabold hover:bg-mint-500 transition disabled:opacity-60">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
                    </svg>
                    <span data-invite-submit-label>ابعت الدعوة</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modal = document.querySelector('[data-invite-modal]');
    if (!modal) return;
    const backdrop    = modal.querySelector('[data-invite-backdrop]');
    const closeBtns   = modal.querySelectorAll('[data-invite-close]');
    const nameEl      = modal.querySelector('[data-invite-name]');
    const phoneEl     = modal.querySelector('[data-invite-phone]');
    const bodyEl      = modal.querySelector('[data-invite-body]');
    const form        = modal.querySelector('[data-invite-form]');
    const submitBtn   = modal.querySelector('[data-invite-submit]');
    const submitLabel = modal.querySelector('[data-invite-submit-label]');

    function open()  { modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function close() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        submitBtn.disabled = false;
        submitLabel.textContent = 'ابعت الدعوة';
    }

    backdrop.addEventListener('click', close);
    closeBtns.forEach(b => b.addEventListener('click', close));

    document.querySelectorAll('[data-invite-open]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const previewUrl = btn.dataset.inviteUrl;
            const sendUrl    = btn.dataset.inviteSendUrl;
            bodyEl.textContent = 'بنحضّر الرسالة...';
            nameEl.textContent = '—';
            phoneEl.textContent = '—';
            form.action = sendUrl;
            open();
            try {
                const r = await fetch(previewUrl, { headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                const body = await r.json();
                if (!body.ok) { bodyEl.textContent = 'فشل تحميل المعاينة.'; return; }
                nameEl.textContent  = body.name || '—';
                phoneEl.textContent = body.phone || '—';
                bodyEl.textContent  = body.message || '';
            } catch (e) {
                bodyEl.textContent = 'فشل الاتصال بالخادم.';
            }
        });
    });

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitLabel.textContent = 'جارٍ الإرسال...';
    });
})();
</script>
@endpush
@endonce
