@extends('layouts.app', ['title' => 'محادثة · ' . ($other->username ?? '')])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('chat.inbox') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        @if($other)
            <a href="{{ route('profile.show', $other->username) }}" class="flex items-center gap-2">
                <x-avatar :user="$other" size="sm"/>
                <div class="leading-tight">
                    <div class="font-bold text-ink-950">{{ $other->username }}</div>
                    @if($other->isOnline())
                        <div class="text-[10px] text-mint-700 font-bold inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span>
                            أونلاين الآن
                        </div>
                    @elseif($other->last_seen_at)
                        <div class="text-[10px] text-ink-400">آخر ظهور {{ $other->last_seen_at->diffForHumans() }}</div>
                    @endif
                </div>
            </a>
            <button type="button"
                    data-report="{{ route('chat.report', $thread) }}"
                    class="ms-auto w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-400 hover:text-blush-500 hover:bg-blush-100 transition"
                    aria-label="ابلاغ">
                <x-icon name="flag" class="w-4 h-4"/>
            </button>
        @endif
    </div>

    <div class="card-light p-3 mb-3 max-h-[60vh] overflow-y-auto space-y-2"
         id="chat-msgs"
         data-thread-id="{{ $thread->id }}"
         data-poll-url="{{ route('chat.poll', $thread) }}"
         data-last-id="{{ $messages->last()->id ?? 0 }}">
        @forelse($messages as $m)
            @php $mine = $m->user_id === auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $m->id }}">
                <div class="max-w-[75%] px-3 py-2 rounded-2xl {{ $mine ? 'bg-coral-500 text-white' : 'bg-cream-100 text-ink-950' }}">
                    <p class="text-sm whitespace-pre-line">{{ $m->body }}</p>
                    <div class="text-[9px] {{ $mine ? 'text-white/70' : 'text-ink-400' }} mt-0.5">{{ $m->created_at?->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <p data-empty class="text-center text-xs text-ink-400 py-8">مفيش رسايل لسه — ابعت أول رسالة.</p>
        @endforelse
    </div>

    <form id="chat-form" method="POST" action="{{ route('chat.send', $thread) }}" class="card-light p-2 flex items-center gap-2">
        @csrf
        <textarea name="body" required rows="1" maxlength="5000" placeholder="اكتب رسالة…"
                  class="flex-1 bg-cream-100 rounded-2xl px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none"></textarea>
        <button class="btn-primary !py-2 !px-4 text-sm shrink-0">ابعت</button>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const list  = document.getElementById('chat-msgs');
    const form  = document.getElementById('chat-form');
    if (!list || !form) return;

    const csrf  = document.querySelector('meta[name="csrf-token"]')?.content;
    const myAvatarTime = (timeStr) => `<div class="text-[9px] text-white/70 mt-0.5">${timeStr || 'الآن'}</div>`;
    const otherTime    = (timeStr) => `<div class="text-[9px] text-ink-400 mt-0.5">${timeStr || 'الآن'}</div>`;
    const escape = (s) => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const renderMsg = (m) => {
        const mine = m.mine;
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
        wrap.dataset.msgId = m.id;
        wrap.innerHTML = `
            <div class="max-w-[75%] px-3 py-2 rounded-2xl ${mine ? 'bg-coral-500 text-white' : 'bg-cream-100 text-ink-950'}">
                <p class="text-sm whitespace-pre-line">${escape(m.body || '')}</p>
                ${mine ? myAvatarTime(m.time) : otherTime(m.time)}
            </div>`;
        return wrap;
    };

    const append = (m) => {
        // De-dup by id
        if (list.querySelector('[data-msg-id="' + m.id + '"]')) return;
        list.querySelector('[data-empty]')?.remove();
        list.appendChild(renderMsg(m));
        list.scrollTop = list.scrollHeight;
        if (m.id > Number(list.dataset.lastId || 0)) list.dataset.lastId = m.id;
    };

    // Scroll to bottom on load
    list.scrollTop = list.scrollHeight;

    // ── AJAX send (optimistic) ──
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const ta  = form.querySelector('textarea');
        const txt = ta.value.trim();
        if (!txt) return;
        const btn = form.querySelector('button');
        btn.disabled = true;
        ta.value = '';

        // Optimistic render with a temp id
        const tempId = 'tmp-' + Date.now();
        const tempMsg = renderMsg({ id: tempId, body: txt, mine: true, time: 'الآن' });
        tempMsg.style.opacity = '0.6';
        list.querySelector('[data-empty]')?.remove();
        list.appendChild(tempMsg);
        list.scrollTop = list.scrollHeight;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'body=' + encodeURIComponent(txt),
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('send failed');
            const data = await res.json();
            // Replace temp with real one
            tempMsg.remove();
            if (data.message) append(data.message);
        } catch (err) {
            tempMsg.style.opacity = '0.3';
            tempMsg.querySelector('p').textContent += ' (فشل الإرسال)';
            ta.value = txt;
        } finally {
            btn.disabled = false;
            ta.focus();
        }
    });

    // ── Poll for new messages every 4s ──
    let polling = false;
    const poll = async () => {
        if (polling || document.hidden) return;
        polling = true;
        try {
            const url = list.dataset.pollUrl + '?since=' + encodeURIComponent(list.dataset.lastId || 0);
            const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            (data.messages || []).forEach(append);
        } catch (err) {
            // soft fail
        } finally {
            polling = false;
        }
    };
    setInterval(poll, 4000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
</script>
@endpush
@endsection
