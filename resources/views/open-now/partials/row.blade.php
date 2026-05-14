@php
    /** @var \App\Models\Business $business */
    /** @var bool $confirmed */
    $cm = $business->categoryMeta();
    $cover = optional($business->relationLoaded('photos') ? $business->photos->first() : null)->url
           ?? $business->photo_url
           ?? null;
    $statusLabel = $confirmed ? $business->openStatusLabel() : 'المواعيد غير مؤكدة';
    $reportTo  = config('services.banhawy.support_whatsapp', '01000000000');
    $reportMsg = "تحديث مواعيد على بنهاوي\nالنشاط: {$business->name}\nرابط: ".route('directory.show', $business)."\nالمواعيد الصحيحة: ";
    $reportUrl = 'https://wa.me/'.\App\Services\WaapiService::toIntl($reportTo).'?text='.urlencode($reportMsg);
@endphp
<div class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden">
    <div class="flex items-stretch">
        <a href="{{ route('directory.show', $business) }}" class="w-20 sm:w-24 shrink-0 bg-cream-100 relative block">
            @if($cover)
                <img src="{{ $cover }}" alt="{{ $business->name }}" loading="lazy"
                     class="absolute inset-0 w-full h-full object-cover"
                     onerror="this.style.display='none'">
            @endif
            <div class="absolute inset-0 grid place-items-center text-2xl">{{ $cm['emoji'] ?? '🏪' }}</div>
        </a>
        <div class="flex-1 p-3 min-w-0">
            <a href="{{ route('directory.show', $business) }}" class="block">
                <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                    <h3 class="text-sm font-extrabold text-ink-950 truncate">{{ $business->name }}</h3>
                    @if($business->is_verified)
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-coral-500"><path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Zm-1 13-3.5-3.5L9 10l2 2 5-5 1.5 1.5Z"/></svg>
                    @endif
                </div>
                <div class="text-[11px] text-ink-500 inline-flex items-center gap-2 flex-wrap">
                    <span>{{ $cm['label'] }}</span>
                    @if($business->zone)
                        <span>·</span>
                        <span>{{ $business->zone->name }}</span>
                    @endif
                </div>
                <div class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-extrabold {{ $confirmed ? 'text-mint-700' : 'text-honey-700' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $confirmed ? 'bg-mint-500' : 'bg-honey-500' }}"></span>
                    {{ $statusLabel }}
                </div>
            </a>
            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                @if($business->phone || $business->hotline)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $business->phone ?: $business->hotline) }}"
                       data-track-click="business_call" data-business="{{ $business->id }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-ink-950 text-white text-[11px] font-extrabold hover:bg-ink-800 transition">
                        اتصال
                    </a>
                @endif
                @if($business->whatsapp)
                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}"
                       target="_blank" rel="noopener"
                       data-track-click="business_whatsapp" data-business="{{ $business->id }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-mint-100 text-mint-700 text-[11px] font-extrabold hover:bg-mint-500 hover:text-white transition">
                        <x-icon name="whatsapp" class="w-3 h-3"/>
                        واتساب
                    </a>
                @endif
                @if($business->lat && $business->lng)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}"
                       target="_blank" rel="noopener"
                       data-track-click="business_directions" data-business="{{ $business->id }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-coral-50 text-coral-600 text-[11px] font-extrabold hover:bg-coral-100 transition">
                        الاتجاهات
                    </a>
                @endif
                <a href="{{ $reportUrl }}" target="_blank" rel="noopener"
                   data-track-click="business_report" data-business="{{ $business->id }}"
                   class="ms-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-500 text-[10px] font-extrabold hover:text-honey-700 hover:ring-honey-500/40 transition">
                    المواعيد غلط؟ بلّغنا
                </a>
            </div>
        </div>
    </div>
</div>
