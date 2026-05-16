@php
    // Reuse the same curated icon set as per-item features for visual consistency.
    $featureIcons = ['wifi','snowflake','coffee','utensils','cup','tv','key','car','phone','bell','gear','sofa','bag','gift','heart','leaf','flame','dumbbell','paw','baby','gem','ticket','tag','map-pin','bolt','briefcase','tools','tooth','shirt'];
    $existing = is_array($business->features) ? $business->features : [];
@endphp

<form method="POST"
      action="{{ route('menu.features.update', $business) }}"
      class="card-light p-4 mb-4 border-mint-500/15 bg-mint-50/40">
    @csrf
    <input type="hidden" name="features" data-features-json
           value="{{ $existing ? json_encode($existing, JSON_UNESCAPED_UNICODE) : '' }}">

    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex items-start gap-2.5 min-w-0">
            <span class="w-8 h-8 rounded-xl bg-mint-100 text-mint-700 grid place-items-center shrink-0">
                <x-icon name="bolt" class="w-4 h-4"/>
            </span>
            <div class="min-w-0">
                <h3 class="text-sm font-extrabold text-ink-950">مميزات النشاط</h3>
                <p class="text-[11px] text-ink-500 mt-0.5 leading-relaxed">اللي بيميّز محلك — توصيل، تكييف، واي فاي، عائلي… بتظهر في صفحتك العامة.</p>
            </div>
        </div>
        <button class="bg-mint-500 hover:bg-mint-600 text-white font-extrabold rounded-full px-4 py-1.5 text-[11px] inline-flex items-center gap-1 transition shrink-0">
            <x-icon name="check" class="w-3 h-3"/>
            حفظ
        </button>
    </div>

    <div class="bg-white rounded-xl border border-ink-950/8 p-2.5" data-features-app>
        <div class="flex items-center gap-1.5 mb-2">
            <div class="relative shrink-0">
                <button type="button" data-icon-toggle
                        class="w-9 h-9 rounded-lg bg-cream-100 hover:bg-cream-200 border border-ink-950/8 grid place-items-center text-ink-700 transition"
                        aria-label="اختر أيقونة">
                    <span data-current-icon class="inline-flex">
                        <x-icon name="tag" class="w-4 h-4"/>
                    </span>
                </button>
                <div data-icon-grid hidden
                     class="absolute z-30 top-full mt-1 start-0 w-[220px] bg-white rounded-xl border border-ink-950/10 shadow-lg p-2 grid grid-cols-6 gap-1">
                    @foreach($featureIcons as $ic)
                        <button type="button"
                                data-icon-pick="{{ $ic }}"
                                class="w-8 h-8 rounded-md hover:bg-cream-100 grid place-items-center text-ink-700 transition"
                                aria-label="{{ $ic }}">
                            <x-icon name="{{ $ic }}" class="w-4 h-4"/>
                        </button>
                    @endforeach
                </div>
            </div>
            <input type="text" data-feature-label maxlength="40"
                   placeholder="ميزة جديدة — مثلاً: توصيل، عائلي، شيشة"
                   class="flex-1 bg-cream-100/60 rounded-lg px-3 py-2 text-ink-950 placeholder-ink-400 outline-0 border border-transparent focus:border-mint-500 focus:bg-white transition text-sm">
            <button type="button" data-feature-add
                    class="px-3 py-2 rounded-lg bg-coral-500 hover:bg-coral-600 text-white text-xs font-extrabold transition shrink-0">
                ضيف
            </button>
        </div>

        <div data-feature-chips class="flex flex-wrap gap-1.5"></div>

        @if(empty($existing))
            <p class="text-[10px] text-ink-400 mt-1.5">لسه مفيش مميزات. ضيف اللي بيميّزك واحفظ.</p>
        @endif
    </div>
</form>
