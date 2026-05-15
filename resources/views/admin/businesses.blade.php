@extends('admin.layouts.admin', ['title' => 'النشاطات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">النشاطات</h1>
    <span class="text-ink-500 text-sm">{{ $businesses->total() }}</span>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.businesses') }}"
       class="px-3 py-1.5 rounded-full text-xs font-bold {{ ! $filter ? 'bg-coral-500 text-ink-950' : 'bg-cream-100 text-ink-500 border border-ink-950/8' }}">الكل</a>
    @foreach(['pending'=>'بانتظار التوثيق','verified'=>'موثّقة','inactive'=>'مخفية'] as $key => $label)
        <a href="{{ route('admin.businesses', ['filter'=>$key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold {{ $filter === $key ? 'bg-coral-500 text-ink-950' : 'bg-cream-100 text-ink-500 border border-ink-950/8' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="a-card overflow-x-auto">
    <table class="a-table w-full">
        <thead>
            <tr>
                <th>النشاط</th>
                <th>النوع</th>
                <th>المنطقة</th>
                <th>المالك</th>
                <th>تليفون</th>
                <th>التوثيق</th>
                <th>نشط</th>
                <th>أنشئ</th>
                <th class="text-end">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($businesses as $b)
                @php $sm = $b->subTypeMeta(); $cm = $b->categoryMeta(); @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($b->photo_url)
                                <img src="{{ $b->photo_url }}" class="w-9 h-9 rounded-lg object-cover" alt="">
                            @else
                                <span class="w-9 h-9 rounded-lg grid place-items-center text-lg" style="background: {{ $cm['color'] }}30">{{ $b->emoji ?: $sm['emoji'] }}</span>
                            @endif
                            <a href="{{ route('directory.show', $b) }}" target="_blank" class="font-bold hover:text-coral-400">{{ $b->name }}</a>
                        </div>
                    </td>
                    <td class="text-ink-500">{{ $sm['label'] }}</td>
                    <td>{{ $b->zone?->name ?? '—' }}</td>
                    <td>
                        @if($b->owner)
                            <a href="{{ route('profile.show', $b->owner->username) }}" target="_blank" class="text-coral-400 text-xs">{{ $b->owner->username }}</a>
                        @else
                            <span class="text-ink-400 text-xs">seed</span>
                        @endif
                    </td>
                    <td dir="ltr" class="text-ink-500 text-xs">{{ $b->phone ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.verify', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_verified ? 'pill-mint' : 'bg-cream-200 text-ink-400' }}">
                                {{ $b->is_verified ? '✓ موثّق' : '— غير موثّق' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.toggle', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_active ? 'bg-cream-200 text-ink-500' : 'pill-blush' }}">
                                {{ $b->is_active ? 'نشط' : 'مخفي' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        @if($b->isPromoted())
                            <span class="a-pill bg-honey-500 text-ink-950" title="ينتهي {{ $b->promoted_until->translatedFormat('d M') }}">
                                ⭐ {{ (int) now()->diffInDays($b->promoted_until, false) }} يوم
                            </span>
                            <form method="POST" action="{{ route('admin.businesses.promote', $b) }}" class="inline">
                                @csrf
                                <input type="hidden" name="days" value="0">
                                <button class="a-pill bg-blush-100 text-blush-500 px-2 py-1" title="إلغاء الترويج">✕</button>
                            </form>
                        @else
                            <div class="inline-flex gap-1">
                                @foreach([7, 30] as $d)
                                    <form method="POST" action="{{ route('admin.businesses.promote', $b) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="days" value="{{ $d }}">
                                        <button class="a-pill bg-honey-100 text-honey-700 hover:bg-honey-500 hover:text-ink-950 transition px-2 py-1" title="روّج لمدة {{ $d }} يوم">
                                            ⭐ {{ $d }}ي
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="text-[10px] text-ink-400">{{ $b->created_at->diffForHumans(short: true) }}</td>
                    <td class="text-end">
                        <div class="inline-flex items-center gap-1">
                            {{-- "ابعت دعوة" — only for unowned businesses that have a phone we can WhatsApp. --}}
                            @if(! $b->owner_user_id && ($b->whatsapp || $b->phone))
                                @php
                                    $isInvited = (bool) $b->invited_at;
                                    $inviteTitle = $isInvited
                                        ? 'اتبعت في ' . $b->invited_at->translatedFormat('d M · h:i a')
                                        : 'ابعت دعوة استلام الصفحة على واتساب';
                                @endphp
                                <button type="button"
                                        data-invite-open
                                        data-invite-url="{{ route('admin.businesses.invite.preview', $b) }}"
                                        data-invite-send-url="{{ route('admin.businesses.invite.send', $b) }}"
                                        class="a-pill px-2.5 py-1.5 transition {{ $isInvited ? 'bg-mint-100 text-mint-700' : 'bg-honey-100 text-honey-700 hover:bg-honey-500 hover:text-ink-950' }}"
                                        title="{{ $inviteTitle }}">
                                    @if($isInvited)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        اتبعت
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
                                        </svg>
                                        دعوة
                                    @endif
                                </button>
                            @endif
                            <a href="{{ route('directory.edit', $b) }}"
                               class="a-pill bg-coral-100 text-coral-700 hover:bg-coral-500 hover:text-white px-2.5 py-1.5 transition" title="تعديل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                                </svg>
                                تعديل
                            </a>
                            <form method="POST" action="{{ route('directory.destroy', $b) }}" class="inline"
                                  data-confirm="حذف النشاط نهائياً؟"
                                  data-confirm-body="مش هيرجع تاني — لكن البيانات هتفضل في الـ DB كـ inactive."
                                  data-confirm-action="احذف"
                                  data-confirm-tone="danger">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-pill bg-cream-200 text-ink-400 hover:bg-blush-500 hover:text-white px-2 py-1.5 transition" title="حذف">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $businesses->links() }}</div>

{{-- ─── Invite-preview modal ────────────────────────────────────
     Shows the templated WhatsApp message + the destination phone
     before the admin confirms the send. --}}
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
                <p data-invite-sent class="hidden mt-2 text-[11px] font-bold text-honey-700">
                    ⚠ اتبعت قبل كده · <span data-invite-sent-at></span>
                </p>
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
                        class="flex-[2] inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full bg-mint-600 text-white text-sm font-extrabold hover:bg-mint-500 transition disabled:opacity-60">
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
    const modal       = document.querySelector('[data-invite-modal]');
    if (!modal) return;
    const backdrop    = modal.querySelector('[data-invite-backdrop]');
    const closeBtns   = modal.querySelectorAll('[data-invite-close]');
    const nameEl      = modal.querySelector('[data-invite-name]');
    const phoneEl     = modal.querySelector('[data-invite-phone]');
    const bodyEl      = modal.querySelector('[data-invite-body]');
    const sentEl      = modal.querySelector('[data-invite-sent]');
    const sentAtEl    = modal.querySelector('[data-invite-sent-at]');
    const form        = modal.querySelector('[data-invite-form]');
    const submitBtn   = modal.querySelector('[data-invite-submit]');
    const submitLabel = modal.querySelector('[data-invite-submit-label]');
    const csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function open() { modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function close() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        sentEl.classList.add('hidden');
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
            sentEl.classList.add('hidden');
            form.action = sendUrl;
            open();
            try {
                const r = await fetch(previewUrl, { headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                const body = await r.json();
                if (!body.ok) { bodyEl.textContent = 'فشل تحميل المعاينة.'; return; }
                nameEl.textContent  = body.name || '—';
                phoneEl.textContent = body.phone || '—';
                bodyEl.textContent  = body.message || '';
                if (body.sent_at) {
                    sentAtEl.textContent = body.sent_at;
                    sentEl.classList.remove('hidden');
                }
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
@endsection
