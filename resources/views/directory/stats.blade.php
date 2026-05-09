@extends('layouts.app', ['title' => 'إحصائيات · ' . $business->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">📊 إحصائيات</h1>
    </div>

    <div class="card-light p-4 mb-3">
        <div class="text-xs text-ink-500 mb-1">{{ $business->name }}</div>
        @if($business->isPromoted())
            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 mb-2">
                ⭐ مُروَّج لحد {{ $business->promoted_until->translatedFormat('d M') }}
            </span>
        @elseif($business->is_verified)
            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-mint-100 text-mint-700 mb-2">
                <x-icon name="check" class="w-3 h-3"/> موثّق
            </span>
        @endif
    </div>

    {{-- Big numbers --}}
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="card-light p-4">
            <div class="text-[11px] font-bold text-ink-500 mb-1">👀 مشاهدات</div>
            <div class="text-3xl font-black text-ink-950">{{ number_format($business->views_count) }}</div>
        </div>
        <div class="card-light p-4">
            <div class="text-[11px] font-bold text-ink-500 mb-1">⭐ التقييم</div>
            <div class="text-3xl font-black text-coral-600">{{ $business->rating_avg }}</div>
            <div class="text-[10px] text-ink-400">من {{ $business->ratings_count }} تقييم</div>
        </div>
        <div class="card-light p-4">
            <div class="text-[11px] font-bold text-ink-500 mb-1">📞 ضغطات اتصال</div>
            <div class="text-3xl font-black text-ink-950">{{ number_format($business->phone_clicks) }}</div>
            @if($business->views_count > 0)
                <div class="text-[10px] text-ink-400">
                    معدل التحويل: {{ round(($business->phone_clicks / $business->views_count) * 100, 1) }}%
                </div>
            @endif
        </div>
        <div class="card-light p-4">
            <div class="text-[11px] font-bold text-ink-500 mb-1">💬 ضغطات واتساب</div>
            <div class="text-3xl font-black text-mint-700">{{ number_format($business->whatsapp_clicks) }}</div>
            @if($business->views_count > 0)
                <div class="text-[10px] text-ink-400">
                    معدل التحويل: {{ round(($business->whatsapp_clicks / $business->views_count) * 100, 1) }}%
                </div>
            @endif
        </div>
    </div>

    <div class="card-light p-4 mb-3">
        <div class="text-[11px] font-bold text-ink-500 mb-2">📝 المراجعات</div>
        <div class="text-2xl font-black text-ink-950">{{ $business->reviews_count }}</div>
        <a href="{{ route('directory.show', $business) }}#reviews" class="text-xs text-coral-600 font-bold hover:underline">شوف كلها →</a>
    </div>

    {{-- Promotion offer --}}
    @if(! $business->isPromoted())
        <div class="card-light !shadow-none border-honey-500/40 bg-honey-50 p-4">
            <h3 class="font-extrabold text-ink-950 mb-1">⭐ روّج نشاطك</h3>
            <p class="text-xs text-ink-500 leading-relaxed mb-3">
                نشاطك المروّج يظهر فوق نتايج البحث في كل القسم لمدة ٧ أو ٣٠ يوم.
                <br>هتلاقي ضغطات اتصال × ٣ أو ٤ في المتوسط.
            </p>
            <a href="https://wa.me/201022345504?text={{ urlencode('عاوز أروّج نشاط: ' . $business->name) }}" target="_blank"
               class="btn-primary w-full justify-center !py-3" style="background: linear-gradient(135deg, #25D366, #128C7E)">
                <x-icon name="whatsapp" class="w-4 h-4"/>
                كلّمنا على واتساب
            </a>
        </div>
    @endif
</div>
@endsection
