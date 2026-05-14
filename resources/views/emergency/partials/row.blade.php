@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $phone = $business->phone ?: $business->hotline;
@endphp
<div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 flex items-center gap-3">
    <span class="w-11 h-11 rounded-2xl bg-blush-100 text-blush-600 grid place-items-center text-xl shrink-0">
        {{ $cm['emoji'] ?? '🚨' }}
    </span>
    <div class="flex-1 min-w-0">
        <a href="{{ route('directory.show', $business) }}" class="block">
            <div class="text-sm font-extrabold text-ink-950 truncate">{{ $business->name }}</div>
            <div class="text-[11px] text-ink-500 inline-flex items-center gap-1.5">
                @if($business->zone)
                    <span>{{ $business->zone->name }}</span>
                @endif
                @if($business->is_24h)
                    <span class="inline-flex items-center gap-1 text-mint-700 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span> ٢٤ ساعة
                    </span>
                @endif
            </div>
        </a>
    </div>
    <div class="flex items-center gap-1.5 shrink-0">
        @if($phone)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"
               data-track-click="phone" data-business="{{ $business->id }}"
               class="w-10 h-10 rounded-full bg-blush-500 text-white grid place-items-center hover:bg-blush-600 transition"
               aria-label="اتصل">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </a>
        @endif
        @if($business->lat && $business->lng)
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}"
               target="_blank" rel="noopener"
               data-track-click="directions" data-business="{{ $business->id }}"
               class="w-10 h-10 rounded-full bg-coral-50 text-coral-600 grid place-items-center hover:bg-coral-100 transition"
               aria-label="الاتجاهات">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M21.71 11.29 12.71 2.29a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42l9 9a1 1 0 0 0 1.42 0l9-9a1 1 0 0 0 0-1.42z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            </a>
        @endif
    </div>
</div>
