@extends('layouts.app', ['title' => 'الإشعارات · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">الإشعارات</h1>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-3xl px-6 pt-14 pb-12 text-center ring-1 ring-ink-950/6">
            <div class="relative w-28 h-28 mx-auto mb-6">
                {{-- soft outer halo --}}
                <span class="absolute inset-0 rounded-full bg-coral-50"></span>
                <span class="absolute inset-3 rounded-full bg-coral-100/70"></span>
                {{-- bell --}}
                <span class="absolute inset-0 grid place-items-center text-coral-600">
                    <x-icon name="bell" class="w-11 h-11"/>
                </span>
                {{-- small zero badge --}}
                <span class="absolute -top-1 -end-1 min-w-7 h-7 px-2 rounded-full bg-white ring-1 ring-ink-950/8 grid place-items-center text-xs font-black text-ink-950">0</span>
            </div>
            <h3 class="text-xl font-black text-ink-950 mb-1.5">هدوء تام</h3>
            <p class="text-ink-500 text-sm leading-relaxed max-w-xs mx-auto">مفيش إشعارات لسه. لما يحصل أي جديد يخصك، هتلاقيه هنا.</p>
            <div class="mt-6 flex items-center justify-center gap-2">
             
            </div>
        </div>
    @else
        <div class="card-light p-2 space-y-1" id="notif-list">
            @foreach($notifications as $n)
                <div class="notif-swipe" data-notif-id="{{ $n->id }}">
                    {{-- Delete background, revealed when content is swiped --}}
                    <div class="notif-swipe-bg" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/>
                            <path d="M14 11v6"/>
                        </svg>
                        <span>حذف</span>
                    </div>
                    <a href="{{ $n->url ?? '#' }}"
                       class="notif-swipe-content flex items-start gap-3 p-3 rounded-xl hover:bg-cream-100 transition {{ ! $n->read_at && $loop->first ? 'bg-coral-50' : '' }}">
                        <span class="w-9 h-9 rounded-full pill-coral grid place-items-center shrink-0 mt-0.5">
                            <x-icon name="bell" class="w-4 h-4"/>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-ink-950">{{ $n->title }}</div>
                            @if($n->body)
                                <div class="text-xs text-ink-500 mt-0.5 line-clamp-2">{{ $n->body }}</div>
                            @endif
                            <div class="text-[10px] text-ink-400 mt-1">{{ $n->created_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    (() => {
        const list = document.getElementById('notif-list');
        if (!list) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        list.querySelectorAll('.notif-swipe').forEach(setupSwipe);

        function setupSwipe(wrap) {
            const content = wrap.querySelector('.notif-swipe-content');
            if (!content) return;

            let startX = 0, lastX = 0, dx = 0;
            let dragging = false, decided = false, isHorizontal = false;
            const startThreshold = 6;       // px before we commit to a direction
            const deleteThreshold = 0.45;   // 45% of width = delete

            const onStart = (clientX) => {
                startX = clientX;
                lastX = clientX;
                dx = 0;
                dragging = true;
                decided = false;
                isHorizontal = false;
                content.style.transition = 'none';
            };

            const onMove = (clientX, clientY, startY) => {
                if (!dragging) return;
                lastX = clientX;
                dx = clientX - startX;

                if (!decided && Math.abs(dx) > startThreshold) {
                    decided = true;
                    isHorizontal = true;
                }
                if (decided && isHorizontal) {
                    content.style.transform = `translateX(${dx}px)`;
                }
            };

            const onEnd = async () => {
                if (!dragging) return;
                dragging = false;
                content.style.transition = '';

                if (!isHorizontal) {
                    content.style.transform = '';
                    return;
                }
                const width = wrap.offsetWidth || 1;
                const past = Math.abs(dx) / width > deleteThreshold;

                if (past) {
                    // Slide out fully in the direction of the swipe, then remove
                    const target = dx > 0 ? width : -width;
                    content.style.transform = `translateX(${target}px)`;
                    wrap.style.maxHeight   = wrap.offsetHeight + 'px';
                    requestAnimationFrame(() => {
                        wrap.style.transition = 'max-height .25s ease, opacity .25s ease, margin .25s ease';
                        wrap.style.maxHeight  = '0';
                        wrap.style.opacity    = '0';
                        wrap.style.marginTop  = '0';
                    });

                    const id = wrap.dataset.notifId;
                    try {
                        await fetch(`/notifications/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                    } catch (e) { /* keep UI removed; user can refresh if needed */ }

                    setTimeout(() => wrap.remove(), 300);
                } else {
                    // Snap back
                    content.style.transform = '';
                }
            };

            // Touch
            wrap.addEventListener('touchstart', (e) => {
                onStart(e.touches[0].clientX);
            }, { passive: true });
            wrap.addEventListener('touchmove',  (e) => {
                onMove(e.touches[0].clientX);
                if (decided && isHorizontal) {
                    // prevent the page from scrolling once we know it's a horizontal swipe
                    e.preventDefault();
                }
            }, { passive: false });
            wrap.addEventListener('touchend',   onEnd);
            wrap.addEventListener('touchcancel', () => {
                dragging = false;
                content.style.transform = '';
                content.style.transition = '';
            });

            // Mouse (desktop testing)
            wrap.addEventListener('mousedown', (e) => {
                onStart(e.clientX);
                const move = (ev) => onMove(ev.clientX);
                const up = () => {
                    onEnd();
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);
                };
                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            });

            // Cancel link click when the user actually swiped
            content.addEventListener('click', (e) => {
                if (Math.abs(lastX - startX) > startThreshold) {
                    e.preventDefault();
                }
            });
        }
    })();
</script>
@endpush
@endsection
