@php
    $prayer = \App\Services\PrayerTimesService::forBanha();
@endphp

@if($prayer)
    @php
        $times  = $prayer['times'] ?? [];
        $pretty = $prayer['pretty'] ?? array_map(
            fn ($v) => $v ? \App\Services\PrayerTimesService::format12($v) : null,
            $times
        );
        $next      = $prayer['next'] ?? null;
        $hours     = $next['in_minutes'] !== null ? intdiv($next['in_minutes'], 60) : null;
        $mins      = $next['in_minutes'] !== null ? $next['in_minutes'] % 60 : null;
        $countdown = $next['in_minutes'] !== null
            ? ($hours > 0 ? "{$hours}س " : '') . "{$mins}د"
            : 'بكرة';
        $today        = $prayer['date'];
        $notifyOn     = auth()->check() && auth()->user()->prayer_notify;
        $isAuthed     = auth()->check();
    @endphp

    <details id="prayer" class="card-light p-3 mb-4 group scroll-mt-20" data-prayer-widget data-today="{{ $today }}">
        <summary class="flex items-center gap-3 cursor-pointer list-none">
            <span class="w-10 h-10 rounded-2xl bg-mint-100 text-mint-700 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M2 22h20"/><path d="M5 22V8h14v14"/><path d="M8 22v-8h8v8"/><circle cx="12" cy="6" r="2"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-[11px] text-ink-500">@if($next) {{ $next['label'] }} · باقي @endif</div>
                <div class="text-sm font-extrabold text-ink-950">
                    @if($next)
                        <span class="text-coral-600">{{ $next['pretty_time'] ?? \App\Services\PrayerTimesService::format12($next['time'] ?? '00:00') }}</span>
                        <span class="text-ink-500 font-bold mx-1">·</span>
                        <span>{{ $countdown }}</span>
                    @else
                        مواعيد الصلاة
                    @endif
                </div>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 text-ink-400 transition group-open:rotate-180 shrink-0">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </summary>

        {{-- Single notify toggle (server-side) --}}
        @if($isAuthed)
            <div class="mt-3 mb-2 flex items-center gap-3 p-3 rounded-2xl bg-cream-100/70 border border-ink-950/8"
                 data-prayer-notify-toggle>
                <span class="w-9 h-9 rounded-xl {{ $notifyOn ? 'bg-coral-500 text-white' : 'bg-ink-100 text-ink-400' }} grid place-items-center shrink-0 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-extrabold text-ink-950">تنبيه الصلاة</div>
                    <p class="text-[11px] text-ink-500" data-toggle-status>
                        {{ $notifyOn ? 'هتوصلك notification في كل وقت صلاة.' : 'فعّل لتوصلك notification عند كل صلاة.' }}
                    </p>
                </div>
                <button type="button" data-toggle-btn
                        aria-pressed="{{ $notifyOn ? 'true' : 'false' }}"
                        class="w-12 h-7 rounded-full relative transition shrink-0 {{ $notifyOn ? 'bg-mint-500' : 'bg-ink-300' }}">
                    <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition {{ $notifyOn ? 'translate-x-[-1.25rem] rtl:translate-x-5' : '' }}"></span>
                </button>
            </div>
        @else
            <a href="{{ route('login') }}?redirect={{ urlencode(route('feed')) }}"
               class="mt-3 mb-2 flex items-center gap-3 p-3 rounded-2xl bg-cream-100/70 border border-ink-950/8 hover:bg-cream-100 transition">
                <span class="w-9 h-9 rounded-xl bg-ink-100 text-ink-500 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-extrabold text-ink-950">تنبيه الصلاة</div>
                    <p class="text-[11px] text-ink-500">سجّل دخول علشان تشغّل التنبيه.</p>
                </div>
                <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
            </a>
        @endif

        {{-- All five prayer times --}}
        <div class="space-y-1.5">
            @php
                $rows = [
                    'fajr'    => 'الفجر',
                    'dhuhr'   => 'الظهر',
                    'asr'     => 'العصر',
                    'maghrib' => 'المغرب',
                    'isha'    => 'العشاء',
                ];
            @endphp
            @foreach($rows as $k => $label)
                @php $isNext = $next && $next['key'] === $k; @endphp
                <div class="flex items-center justify-between px-3 py-2 rounded-xl {{ $isNext ? 'bg-mint-100' : 'bg-cream-100/60' }}">
                    <span class="text-xs font-extrabold {{ $isNext ? 'text-mint-700' : 'text-ink-950' }}">{{ $label }}</span>
                    <span class="text-xs font-extrabold {{ $isNext ? 'text-mint-700' : 'text-ink-700' }}">{{ $pretty[$k] ?? '—' }}</span>
                </div>
            @endforeach
        </div>

        @if($prayer['hijri'])
            <p class="text-[10px] text-ink-400 text-center mt-3">{{ $prayer['hijri'] }} · بنها</p>
        @endif
    </details>

    @once
    @push('scripts')
    <script>
    (function () {
        const wrap = document.querySelector('[data-prayer-notify-toggle]');
        if (!wrap) return;
        const btn    = wrap.querySelector('[data-toggle-btn]');
        const knob   = btn.querySelector('span');
        const status = wrap.querySelector('[data-toggle-status]');
        const icon   = wrap.querySelector('span:first-child');
        const csrf   = document.querySelector('meta[name="csrf-token"]')?.content;

        async function setEnabled(enabled) {
            // Optimistic UI
            btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            btn.classList.toggle('bg-mint-500', enabled);
            btn.classList.toggle('bg-ink-300', !enabled);
            knob.classList.toggle('translate-x-[-1.25rem]', enabled);
            knob.classList.toggle('rtl:translate-x-5', enabled);
            icon.classList.toggle('bg-coral-500', enabled);
            icon.classList.toggle('text-white', enabled);
            icon.classList.toggle('bg-ink-100', !enabled);
            icon.classList.toggle('text-ink-400', !enabled);
            status.textContent = enabled
                ? 'هتوصلك notification في كل وقت صلاة.'
                : 'فعّل لتوصلك notification عند كل صلاة.';

            try {
                // 1) Make sure browser permission is granted (push won't deliver otherwise)
                if (enabled && 'Notification' in window && Notification.permission === 'default') {
                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') {
                        // Roll back
                        return setEnabled(false);
                    }
                }
                // 2) Ensure server-side push subscription exists (idempotent)
                if (enabled && window.banhawyPush?.subscribe) {
                    await window.banhawyPush.subscribe();
                }
                // 3) Save preference server-side
                await fetch('{{ route('profile.prayer.notify') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'enabled=' + (enabled ? 1 : 0),
                    credentials: 'same-origin',
                });
            } catch (e) { /* ignore — UI already updated optimistically */ }
        }

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const next = btn.getAttribute('aria-pressed') !== 'true';
            setEnabled(next);
        });

        // Auto-open + scroll when arriving via #prayer (e.g. bottom-nav link)
        function openIfHashed() {
            if (window.location.hash !== '#prayer') return;
            const widget = document.getElementById('prayer');
            if (!widget) return;
            widget.open = true;
            widget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        window.addEventListener('hashchange', openIfHashed);
        openIfHashed();
    })();
    </script>
    @endpush
    @endonce
@endif
