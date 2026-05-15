@extends('layouts.app', [
    'title'       => 'احجز موعد · ' . $business->name . ' · بنهاوي',
    'description' => 'احجز موعد إلكتروني عند ' . $business->name . ' من بنهاوي — اختار اليوم والساعة المناسبة لك.',
    'canonical'   => route('booking.show', $business),
])

@php
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $success = session('booking_success');
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Top bar --}}
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">{{ $sm['label'] ?? '' }} · {{ $business->name }}</span>
    </div>

    {{-- Success state --}}
    @if($success)
        @php
            $waStatus = $success['wa_status'] ?? 'pending';
            $hasWa    = $success['has_whatsapp'] ?? false;
            $waCopy = match (true) {
                in_array($waStatus, ['sent', 'simulated'], true) => [
                    'tone'  => 'mint',
                    'icon'  => '✓',
                    'title' => 'تم إرسال حجزك للنشاط على واتساب',
                    'desc'  => 'هيتواصلوا معاك على رقمك قريب لتأكيد الحجز.',
                ],
                $waStatus === 'failed' => [
                    'tone'  => 'honey',
                    'icon'  => '!',
                    'title' => 'اتسجل حجزك — بس الرسالة على واتساب فشلت',
                    'desc'  => $hasWa
                        ? 'هنحاول نبعتها تاني. ممكن كمان تتواصل أنت معاهم على رقم النشاط.'
                        : 'النشاط ده مفيش رقم واتساب مسجل. هنوصلهم الحجز بطريقة تانية.',
                ],
                default => [
                    'tone'  => 'mint',
                    'icon'  => '✓',
                    'title' => 'تم تسجيل حجزك',
                    'desc'  => 'النشاط هيراجع طلبك ويأكدّه قريب.',
                ],
            };
            $toneRing = ['mint' => 'ring-mint-500/30 bg-mint-50', 'honey' => 'ring-honey-500/30 bg-honey-50'][$waCopy['tone']];
            $toneDot  = ['mint' => 'bg-mint-500', 'honey' => 'bg-honey-500'][$waCopy['tone']];
            $toneInner = ['mint' => 'ring-mint-500/20', 'honey' => 'ring-honey-500/20'][$waCopy['tone']];
        @endphp
        <div class="card-light p-5 mb-4 ring-1 {{ $toneRing }}">
            <div class="flex items-center gap-3 mb-3">
                <span class="w-10 h-10 rounded-full {{ $toneDot }} grid place-items-center text-white text-lg font-black">
                    {{ $waCopy['icon'] }}
                </span>
                <div class="flex-1 min-w-0">
                    <h2 class="text-base font-black text-ink-950 leading-tight">{{ $waCopy['title'] }}</h2>
                    <p class="text-[11px] text-ink-500 mt-0.5">رقم الحجز #{{ $success['id'] }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-3 mb-3 ring-1 {{ $toneInner }}">
                <div class="text-[11px] text-ink-500">موعدك</div>
                <div class="text-base font-black text-ink-950">{{ $success['pretty'] }}</div>
            </div>
            <p class="text-xs text-ink-500 leading-relaxed">
                {{ $waCopy['desc'] }}
            </p>
        </div>
    @endif

    {{-- Hero --}}
    <div class="card-light p-4 mb-3">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl grid place-items-center shrink-0"
                  style="background: {{ $cm['color'] ?? '#2D5BFF' }}1A; color: {{ $cm['color'] ?? '#2D5BFF' }};">
                <x-icon :name="$cm['icon'] ?? 'briefcase'" class="w-6 h-6"/>
            </span>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-black text-ink-950 truncate">احجز موعد عند {{ $business->name }}</h1>
                <p class="text-xs text-ink-500 truncate">
                    مدة الموعد: {{ $business->booking_slot_minutes }} دقيقة
                    @if($business->booking_lead_hours > 0)
                        · لازم تحجز قبل {{ $business->booking_lead_hours }} ساعة على الأقل
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Day picker (horizontal scroll) --}}
    <div class="mb-3">
        <h3 class="text-xs font-extrabold text-ink-500 mb-2 px-1">١. اختار اليوم</h3>
        <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1">
            @foreach($days as $d)
                @php $isSel = $d['key'] === $selectedKey; @endphp
                <a href="{{ route('booking.show', ['business' => $business, 'day' => $d['key']]) }}"
                   class="shrink-0 w-16 text-center rounded-2xl py-2.5 transition
                          {{ $isSel ? 'bg-coral-500 text-white shadow-md' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                    <div class="text-[10px] font-bold {{ $isSel ? 'text-white/85' : 'text-ink-500' }}">
                        {{ $d['is_today'] ? 'النهارده' : $d['weekday'] }}
                    </div>
                    <div class="text-base font-black mt-0.5">{{ $d['date']->translatedFormat('d') }}</div>
                    <div class="text-[9px] {{ $isSel ? 'text-white/85' : 'text-ink-400' }}">{{ $d['date']->translatedFormat('M') }}</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Slot grid --}}
    <div class="card-light p-4 mb-3">
        <h3 class="text-xs font-extrabold text-ink-500 mb-3 px-1">٢. اختار الساعة</h3>

        @if(empty($slots))
            <div class="text-center py-8">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mx-auto text-ink-300 mb-2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <p class="text-sm font-bold text-ink-500">النشاط مقفول النهار ده</p>
                <p class="text-xs text-ink-400 mt-1">جرّب يوم تاني</p>
            </div>
        @else
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" data-slot-grid>
                @foreach($slots as $slotData)
                    @php
                        $iso = $slotData['starts_at']->format('Y-m-d\TH:i:s');
                        $tone = $slotData['bookable']
                            ? 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500 hover:bg-coral-50 cursor-pointer'
                            : 'bg-ink-50 text-ink-400 ring-1 ring-ink-950/5 cursor-not-allowed line-through';
                    @endphp
                    <button type="button"
                            data-slot="{{ $iso }}"
                            data-slot-label="{{ $slotData['starts_at']->translatedFormat('l d M · h:i a') }}"
                            @disabled(!$slotData['bookable'])
                            class="py-2.5 px-2 rounded-xl text-sm font-extrabold transition text-center {{ $tone }}">
                        {{ $slotData['label'] }}
                        @if($slotData['bookable'] && $slotData['capacity'] > 1)
                            <span class="block text-[9px] font-bold text-mint-700">{{ $slotData['available'] }} متاحة</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Booking form (revealed once a slot is selected) --}}
    <form method="POST" action="{{ route('booking.store', $business) }}"
          data-booking-form class="card-light p-4 mb-3 hidden">
        @csrf
        <input type="hidden" name="starts_at" data-slot-input value="{{ old('starts_at') }}">

        <h3 class="text-xs font-extrabold text-ink-500 mb-3 px-1">٣. بياناتك</h3>

        <div class="mb-3">
            <div class="text-[11px] text-ink-500 mb-1">الموعد المختار</div>
            <div class="bg-coral-50 rounded-xl px-3 py-2.5 text-sm font-extrabold text-coral-700" data-slot-display>
                —
            </div>
        </div>

        <div class="space-y-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">اسمك</label>
                <input type="text" name="name" required maxlength="80"
                       value="{{ old('name', $user?->name ?? '') }}"
                       placeholder="مثلاً: محمد عبد الرحمن"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم موبايلك</label>
                <input type="tel" name="phone" required pattern="01[0125]\d{8}" maxlength="11" inputmode="numeric"
                       dir="ltr"
                       value="{{ old('phone', $user?->phone ?? '') }}"
                       placeholder="01xxxxxxxxx"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition font-mono">
                @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">ملاحظات (اختياري)</label>
                <textarea name="notes" rows="2" maxlength="500"
                          placeholder="مثلاً: كشف أول مرة، اسم تاني للمريض، إلخ"
                          class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @error('starts_at') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <button type="submit" class="btn-primary w-full justify-center !py-3.5 text-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="20 6 9 17 4 12"/></svg>
                أكد الحجز
            </button>
            <p class="text-[10px] text-ink-400 text-center leading-relaxed">
                بإرسال الحجز، أنت بتأكد إن البيانات صحيحة وإن صاحب النشاط هيتواصل معاك على الرقم اللي كتبته.
            </p>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const grid = document.querySelector('[data-slot-grid]');
    const form = document.querySelector('[data-booking-form]');
    if (!form) return;
    const input = form.querySelector('[data-slot-input]');
    const display = form.querySelector('[data-slot-display]');

    function pick(btn) {
        if (btn.disabled) return;
        // Visual selection
        grid?.querySelectorAll('[data-slot]').forEach(b => {
            b.classList.remove('bg-coral-500', 'text-white', '!ring-coral-500');
            if (!b.disabled) b.classList.add('bg-white', 'text-ink-950');
        });
        btn.classList.remove('bg-white', 'text-ink-950');
        btn.classList.add('bg-coral-500', 'text-white', '!ring-coral-500');

        input.value = btn.dataset.slot;
        display.textContent = btn.dataset.slotLabel;
        form.classList.remove('hidden');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    grid?.querySelectorAll('[data-slot]').forEach(btn => {
        btn.addEventListener('click', () => pick(btn));
    });

    // Restore selection on validation error
    @if(old('starts_at'))
        const old = "{{ old('starts_at') }}";
        const matching = grid?.querySelector('[data-slot="' + old + '"]');
        if (matching) pick(matching);
    @endif
})();
</script>
@endpush
@endsection
