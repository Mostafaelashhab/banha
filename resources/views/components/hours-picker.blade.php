@props(['schedule' => null, 'hoursText' => null])

@php
    $sched = is_array($schedule) ? $schedule : (json_decode($schedule ?: '', true) ?: []);
    $hasSched = ! empty($sched);
    $days = \App\Models\Business::WEEKDAYS;
@endphp

<div data-hours-picker>
    {{-- Toggle: simple text vs weekly schedule --}}
    <div class="flex items-center gap-2 mb-2">
        <label class="text-xs font-bold text-ink-500">المواعيد</label>
        <span class="ms-auto inline-flex bg-cream-100 rounded-full p-0.5 text-[10px] font-bold border border-ink-950/8">
            <button type="button" data-hours-mode="text"
                    class="px-3 py-1 rounded-full transition {{ $hasSched ? 'text-ink-500' : 'bg-coral-500 text-white' }}">
                نص بسيط
            </button>
            <button type="button" data-hours-mode="schedule"
                    class="px-3 py-1 rounded-full transition {{ $hasSched ? 'bg-coral-500 text-white' : 'text-ink-500' }}">
                جدول أسبوعي
            </button>
        </span>
    </div>

    {{-- Mode A: freeform text (always rendered, just hidden when schedule is in use) --}}
    <div data-hours-text-wrap class="{{ $hasSched ? 'hidden' : '' }}">
        <input type="text" name="hours" maxlength="100" value="{{ old('hours', $hoursText ?? '') }}"
               placeholder="مثلاً: يومي ٩ص-١١م"
               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">
        <p class="text-[10px] text-ink-400 mt-1">سطر واحد بسيط — استخدم "جدول أسبوعي" لو ساعاتك تختلف بالأيام.</p>
    </div>

    {{-- Mode B: per-day schedule --}}
    <div data-hours-sched-wrap class="{{ $hasSched ? '' : 'hidden' }} space-y-1.5">
        @foreach($days as $key => $label)
            @php
                $val = old("hours_schedule.$key", $sched[$key] ?? null);
                $isOpen = ! empty($val) && $val !== 'closed';
                [$start, $end] = $isOpen && preg_match('/^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/', $val, $m)
                    ? [$m[1], $m[2]] : ['09:00', '22:00'];
            @endphp
            <div class="flex items-center gap-2 bg-cream-100/60 rounded-xl px-3 py-2"
                 data-day="{{ $key }}">
                <span class="w-14 text-[11px] font-extrabold text-ink-950 shrink-0">{{ $label }}</span>

                <label class="inline-flex items-center gap-1 cursor-pointer">
                    <input type="checkbox" data-day-open value="1" {{ $isOpen ? 'checked' : '' }}
                           class="sr-only peer">
                    <span class="w-9 h-5 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                        <span class="absolute top-0.5 start-0.5 w-4 h-4 rounded-full bg-white shadow transition peer-checked:translate-x-[-0.95rem] rtl:peer-checked:translate-x-4"></span>
                    </span>
                </label>

                <div class="flex-1 flex items-center gap-1.5 {{ $isOpen ? '' : 'opacity-40 pointer-events-none' }}" data-day-times>
                    <input type="time" data-day-start value="{{ $start }}"
                           class="bg-white rounded-lg px-2 py-1 text-[11px] outline-0 border border-ink-950/8 focus:border-coral-500" dir="ltr">
                    <span class="text-ink-400 text-[10px]">→</span>
                    <input type="time" data-day-end value="{{ $end }}"
                           class="bg-white rounded-lg px-2 py-1 text-[11px] outline-0 border border-ink-950/8 focus:border-coral-500" dir="ltr">
                </div>

                {{-- Hidden value in shift "HH:MM-HH:MM" format, or empty when closed --}}
                <input type="hidden" name="hours_schedule[{{ $key }}]"
                       value="{{ $isOpen ? $start.'-'.$end : '' }}" data-day-value>
            </div>
        @endforeach
        <p class="text-[10px] text-ink-400 mt-1">الأيام المغلقة سيبها الـtoggle مقفول. الـapp هيظهر "مفتوح/مغلق دلوقتي" تلقائي.</p>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-hours-picker]').forEach(picker => {
        const textWrap   = picker.querySelector('[data-hours-text-wrap]');
        const schedWrap  = picker.querySelector('[data-hours-sched-wrap]');
        const textInput  = textWrap.querySelector('input[name="hours"]');
        const modeBtns   = picker.querySelectorAll('[data-hours-mode]');

        function setMode(mode) {
            const isSched = mode === 'schedule';
            schedWrap.classList.toggle('hidden', !isSched);
            textWrap.classList.toggle('hidden',  isSched);
            modeBtns.forEach(b => {
                const active = b.dataset.hoursMode === mode;
                b.classList.toggle('bg-coral-500', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('text-ink-500', !active);
            });
            // When switching to schedule mode, blank the freeform field so it doesn't override
            // (and vice versa: clearing schedule rows is implicit since toggles are off by default)
            if (isSched && textInput) textInput.value = '';
        }
        modeBtns.forEach(b => b.addEventListener('click', () => setMode(b.dataset.hoursMode)));

        // Per-day toggle + time inputs → keep hidden value in sync
        schedWrap.querySelectorAll('[data-day]').forEach(row => {
            const toggle = row.querySelector('[data-day-open]');
            const times  = row.querySelector('[data-day-times]');
            const start  = row.querySelector('[data-day-start]');
            const end    = row.querySelector('[data-day-end]');
            const hidden = row.querySelector('[data-day-value]');

            function sync() {
                const open = toggle.checked;
                times.classList.toggle('opacity-40', !open);
                times.classList.toggle('pointer-events-none', !open);
                hidden.value = open ? `${start.value}-${end.value}` : '';
            }
            toggle.addEventListener('change', sync);
            start.addEventListener('change', sync);
            end.addEventListener('change', sync);
        });
    });
})();
</script>
@endpush
@endonce
