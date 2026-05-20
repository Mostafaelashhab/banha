@php
    $tm = $jobRequest->tradeMeta();
@endphp
@extends('layouts.app', [
    'title'       => 'طلب ' . ($tm['label'] ?? 'صنايعي') . ' في ' . ($jobRequest->zone->name ?? 'بنها') . ' · بنها.shop',
    'description' => \Illuminate\Support\Str::limit($jobRequest->description, 160),
])

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('craft-jobs.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500">طلب شغل #{{ $jobRequest->id }}</span>
    </div>

    @if(session('flash'))
        <div class="card-light bg-mint-50 ring-1 ring-mint-500/30 p-3 mb-3 text-sm font-bold text-mint-700">
            {{ session('flash') }}
        </div>
    @endif

    {{-- Hero --}}
    <div class="card-light p-5 mb-3 bg-gradient-to-br from-cream-50 to-cream-100">
        <div class="flex items-start gap-3">
            <span class="w-14 h-14 rounded-2xl bg-coral-500 text-white grid place-items-center text-3xl shrink-0">
                {{ $tm['emoji'] ?? '🔧' }}
            </span>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-black text-ink-950">{{ $tm['label'] ?? $jobRequest->sub_type }}</h1>
                <div class="flex items-center gap-2 flex-wrap mt-1">
                    <span class="text-[11px] text-ink-500">{{ $jobRequest->zone->name ?? 'بنها' }}</span>
                    @php
                        $urgencyColor = match($jobRequest->urgency) {
                            'asap'      => 'bg-blush-500 text-white',
                            'today'     => 'bg-coral-500 text-white',
                            'this_week' => 'bg-honey-100 text-honey-700',
                            default     => 'bg-ink-100 text-ink-500',
                        };
                    @endphp
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $urgencyColor }}">
                        {{ $jobRequest->urgencyLabel() }}
                    </span>
                    @if($jobRequest->status !== 'open')
                        <span class="text-[10px] font-extrabold bg-ink-100 text-ink-700 px-2 py-0.5 rounded-full">
                            {{ $jobRequest->statusLabel() }}
                        </span>
                    @endif
                </div>
                <div class="text-[11px] text-ink-400 mt-1">{{ $jobRequest->created_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="card-light p-4 mb-3">
        <h3 class="text-xs font-bold text-ink-500 mb-2">تفاصيل الشغلانة</h3>
        <p class="text-sm text-ink-950 leading-relaxed whitespace-pre-line">{{ $jobRequest->description }}</p>
    </div>

    {{-- Location + budget --}}
    @if($jobRequest->address || $jobRequest->budgetLabel())
        <div class="card-light p-4 mb-3 space-y-2">
            @if($jobRequest->address)
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                        <x-icon name="map-pin" class="w-4 h-4"/>
                    </span>
                    <div>
                        <div class="text-[10px] text-ink-500">العنوان</div>
                        <div class="text-sm font-bold text-ink-950">{{ $jobRequest->address }}</div>
                    </div>
                </div>
            @endif
            @if($jobRequest->budgetLabel())
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-mint-50 text-mint-700 grid place-items-center shrink-0">💰</span>
                    <div>
                        <div class="text-[10px] text-ink-500">الميزانية</div>
                        <div class="text-sm font-bold text-ink-950">{{ $jobRequest->budgetLabel() }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Response form (only for craftsmen with matching trade) --}}
    @if($jobRequest->status === 'open')
        @auth
            @if($myEligibleBusinesses->isNotEmpty())
                <div class="card-light p-4 mb-3 ring-2 ring-coral-500/30 bg-coral-50/40">
                    <h3 class="text-sm font-extrabold text-ink-950 mb-1">📨 رد على الطلب ده</h3>
                    <p class="text-[11px] text-ink-500 mb-3 leading-relaxed">
                        رد بسعر مبدأي وملاحظة قصيرة. <strong>بنها.shop</strong> يبعت ردك للعميل تلقائياً
                        على واتساب ونوتيفيكيشن، ويبعتلك بيانات العميل في نفس الوقت.
                    </p>

                    @if($existingResponse)
                        {{-- After-response success card --}}
                        <div class="bg-mint-50 ring-1 ring-mint-500/30 rounded-2xl p-3 mb-3">
                            <div class="flex items-start gap-2">
                                <span class="w-6 h-6 rounded-full bg-mint-500 text-white grid place-items-center text-xs shrink-0 font-black">✓</span>
                                <div class="flex-1">
                                    <div class="text-sm font-extrabold text-mint-700">تم إرسال ردك للعميل</div>
                                    <div class="text-[11px] text-ink-600 mt-1 leading-relaxed">
                                        رقم العميل: <code class="font-bold text-coral-700" dir="ltr">{{ $jobRequest->phone }}</code>
                                        — اتصل بيه دلوقتي قبل ما حد يسبقك.
                                    </div>
                                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($jobRequest->phone) }}"
                                       target="_blank" rel="noopener"
                                       class="mt-2 inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-white text-[11px] font-extrabold"
                                       style="background: linear-gradient(135deg, #25D366, #128C7E)">
                                        <x-icon name="whatsapp" class="w-3.5 h-3.5"/>
                                        افتح واتساب العميل
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('craft-jobs.respond', $jobRequest) }}" class="space-y-3">
                        @csrf
                        @if($myEligibleBusinesses->count() > 1)
                            <select name="business_id" required
                                    class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm outline-0 border border-ink-950/8">
                                @foreach($myEligibleBusinesses as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="business_id" value="{{ $myEligibleBusinesses->first()->id }}">
                            <div class="text-[11px] text-ink-500">باسم نشاطك: <strong class="text-ink-950">{{ $myEligibleBusinesses->first()->name }}</strong></div>
                        @endif

                        <input type="number" name="quoted_price" min="0" max="1000000"
                               value="{{ $existingResponse->quoted_price ?? '' }}"
                               placeholder="سعر مبدأي (ج) — اختياري" dir="ltr" inputmode="numeric"
                               class="w-full bg-cream-100 rounded-xl px-4 py-3 text-sm outline-0 border border-ink-950/8 font-mono">

                        <textarea name="note" rows="2" maxlength="300"
                                  placeholder="ملاحظة قصيرة (اختياري) — مثلاً: 'أقدر أجي خلال ساعة'"
                                  class="w-full bg-cream-100 rounded-xl px-4 py-2.5 text-sm outline-0 border border-ink-950/8 resize-none">{{ $existingResponse->note ?? '' }}</textarea>

                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-full font-extrabold text-white text-sm shadow-lg bg-coral-500 hover:bg-coral-600 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            {{ $existingResponse ? 'حدّث ردك' : 'أرسل ردك للعميل' }}
                        </button>
                        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
                            بنها.shop هيبعت للعميل واتساب + نوتيفيكيشن فيهم بياناتك، وبنبعتلك واتساب فيه بيانات العميل.
                        </p>
                    </form>
                </div>
            @else
                <div class="card-light p-4 mb-3 bg-cream-50 text-center">
                    <p class="text-xs text-ink-600 mb-2">
                        ليك حساب على بنها.shop لكن مسجّلش كصنايعي في تخصص <strong>{{ $tm['label'] }}</strong>.
                    </p>
                    <a href="{{ route('craftsmen.signup') . '?trade=' . $jobRequest->sub_type }}"
                       class="inline-flex py-2 px-4 rounded-full bg-coral-500 text-white text-xs font-extrabold">
                        سجّل كـ {{ $tm['label'] }} → رد على الطلب
                    </a>
                </div>
            @endif
        @else
            <div class="card-light p-4 mb-3 bg-cream-50 text-center">
                <p class="text-xs text-ink-600 mb-2">إنت صنايعي وعاوز ترد؟</p>
                <a href="{{ route('login') . '?redirect=' . urlencode(route('craft-jobs.show', $jobRequest)) }}"
                   class="inline-flex py-2 px-4 rounded-full bg-coral-500 text-white text-xs font-extrabold">
                    سجّل دخول → رد على الطلب
                </a>
            </div>
        @endauth
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-2 text-center mb-4">
        <div class="card-light p-3">
            <div class="text-xs text-ink-500">شاهدوه</div>
            <div class="text-base font-black text-ink-950">{{ $jobRequest->views_count }}</div>
        </div>
        <div class="card-light p-3">
            <div class="text-xs text-ink-500">ردود</div>
            <div class="text-base font-black text-coral-600">{{ $jobRequest->responses_count }}</div>
        </div>
        <div class="card-light p-3">
            <div class="text-xs text-ink-500">الحالة</div>
            <div class="text-base font-black text-mint-700">{{ $jobRequest->statusLabel() }}</div>
        </div>
    </div>
</div>
@endsection
