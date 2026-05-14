@extends('layouts.app', [
    'title' => 'عروض بنها النهارده · مطاعم، كافيهات، محلات، عيادات · بنهاوي',
    'description' => 'كل عروض بنها والقليوبية النهارده — مطاعم، كافيهات، محلات، عيادات، كورسات. اضغط واتصل أو واتساب مباشرة.',
    'keywords' => 'عروض بنها, خصومات بنها, عروض اليوم, عروض مطاعم بنها, عروض كافيهات بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-2xl font-black text-ink-950 mb-1">عروض بنها النهارده</h1>
        <p class="text-[12px] text-ink-500">أحدث العروض من نشاطات بنها والقليوبية — متجدّدة كل يوم.</p>
    </div>

    {{-- ─── Category filter ─── --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 mb-5">
        <div class="flex items-center gap-2 min-w-max">
            <a href="{{ route('offers.index') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ ! $cat ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                الكل
            </a>
            @foreach($cats as $key => $label)
                <a href="{{ route('offers.index', ['cat' => $key]) }}"
                   class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                          {{ $cat === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ─── Empty state ─── --}}
    @if($businesses->isEmpty() && $listings->isEmpty())
        <div class="card-light p-10 text-center rise rise-2">
            <div class="text-5xl mb-3">🎯</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">مفيش عروض شغّالة دلوقتي</h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto leading-relaxed mb-4">
                لو عندك نشاط في بنها، انشر عرضك ووصلّه لآلاف الزوار.
            </p>
            <a href="{{ route('marketing.claim') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                ضيف عرضك
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5 rtl:rotate-180">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        </div>
    @endif

    {{-- ─── Business offers (promoted) ─── --}}
    @if($businesses->isNotEmpty())
        <section class="mb-6 rise rise-2">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">عروض من نشاطات بنها</h2>
                <span class="text-[10px] font-bold text-ink-500">{{ $businesses->count() }} عرض</span>
            </div>
            <div class="space-y-2">
                @foreach($businesses as $b)
                    @php
                        $cm = $b->categoryMeta();
                        $cover = $b->photos->first()->url ?? $b->photo_url ?? null;
                    @endphp
                    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden">
                        <div class="flex items-stretch">
                            <div class="w-24 sm:w-32 shrink-0 bg-cream-100 relative">
                                @if($cover)
                                    <img src="{{ $cover }}" alt="{{ $b->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 grid place-items-center text-3xl">{{ $cm['emoji'] ?? '🏪' }}</div>
                                @endif
                                <span class="absolute top-1.5 start-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 text-[9px] font-extrabold">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5">
                                        <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
                                    </svg>
                                    عرض
                                </span>
                            </div>
                            <div class="flex-1 p-3 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                    <h3 class="text-sm font-extrabold text-ink-950 truncate">{{ $b->name }}</h3>
                                    @if($b->is_verified)
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-coral-500"><path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Zm-1 13-3.5-3.5L9 10l2 2 5-5 1.5 1.5Z"/></svg>
                                    @endif
                                </div>
                                <div class="text-[11px] text-ink-500 inline-flex items-center gap-2">
                                    <span>{{ $cm['label'] }}</span>
                                    @if($b->zone)
                                        <span>·</span>
                                        <span>{{ $b->zone->name }}</span>
                                    @endif
                                </div>
                                @if($b->description)
                                    <p class="text-[12px] text-ink-950 mt-1.5 leading-snug line-clamp-2">{{ $b->description }}</p>
                                @endif
                                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                    <a href="{{ route('directory.show', $b) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-coral-500 text-white text-[11px] font-extrabold hover:bg-coral-600 transition">
                                        احصل على العرض
                                    </a>
                                    @if($b->whatsapp)
                                        <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($b->whatsapp) }}?text={{ urlencode('شفت العرض على بنهاوي · ممكن تفاصيل أكتر؟') }}"
                                           target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-mint-100 text-mint-700 text-[11px] font-extrabold hover:bg-mint-500 hover:text-white transition">
                                            <x-icon name="whatsapp" class="w-3 h-3"/>
                                            واتساب
                                        </a>
                                    @endif
                                    @if($b->promoted_until)
                                        <span class="ms-auto text-[10px] font-bold text-ink-400">حتى {{ $b->promoted_until->translatedFormat('d M') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Marketplace featured ─── --}}
    @if($listings->isNotEmpty())
        <section class="mb-6 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">عروض من سوق بنها</h2>
                <a href="{{ route('marketplace.index') }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف السوق ←</a>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @foreach($listings as $l)
                    <a href="{{ route('marketplace.show', $l) }}" class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition">
                        <div class="aspect-square bg-coral-50 relative">
                            <div class="absolute inset-0 grid place-items-center text-coral-600/40">
                                @php $clm = \App\Models\Listing::CATEGORIES[$l->category] ?? ['icon' => 'bag']; @endphp
                                <x-icon :name="$clm['icon']" class="w-12 h-12"/>
                            </div>
                            @if($l->photo_url)
                                <img src="{{ $l->photo_url }}" alt="{{ $l->title }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            <span class="absolute top-1.5 start-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 text-[9px] font-extrabold">
                                مميّز
                            </span>
                        </div>
                        <div class="p-2">
                            <div class="text-[12px] font-extrabold text-ink-950 truncate">{{ $l->title }}</div>
                            <div class="text-[13px] font-black text-coral-600 mt-0.5" dir="ltr">{{ $l->priceLabel() }}</div>
                            @if($l->zone)
                                <div class="text-[10px] text-ink-500 mt-0.5 truncate">📍 {{ $l->zone->name }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Owner CTA ─── --}}
    <a href="{{ route('marketing.claim') }}" class="block rounded-3xl p-5 relative overflow-hidden mt-4 rise rise-4" style="background: #1F46DB;">
        <div class="absolute -bottom-12 -start-12 w-44 h-44 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="relative flex items-center gap-3 text-white">
            <span class="w-12 h-12 rounded-2xl bg-white/15 grid place-items-center text-2xl shrink-0">🎯</span>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-extrabold text-white/80">لأصحاب النشاطات</div>
                <div class="text-base font-black leading-tight">انشر عرضك على بنهاوي</div>
                <div class="text-[12px] text-white/85 mt-0.5 leading-snug">يوصل لآلاف الزوار في بنها والقليوبية.</div>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-5 h-5 shrink-0">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </div>
    </a>

</div>
@endsection
