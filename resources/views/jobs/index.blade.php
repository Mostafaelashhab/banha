@extends('layouts.app', [
    'title' => 'وظائف بنها · شغل وفرص عمل في بنها · بنهاوي',
    'description' => 'لوحة وظائف بنها والقليوبية — مطاعم، صيدليات، حضانات، محلات، مصانع. أعلن عن وظيفة أو دور على شغل. مجاناً.',
    'keywords' => 'وظائف بنها, شغل في بنها, فرص عمل بنها, وظائف القليوبية, شغل part time بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-2xl font-black text-ink-950 leading-tight mb-1">وظائف بنها</h1>
        <p class="text-[12px] text-ink-500 leading-relaxed">
            لوحة الشغل المحلية في بنها — مطاعم، محلات، حضانات، مصانع. أعلن مجاناً أو دور على شغل قربك.
        </p>
    </div>

    {{-- ─── Tabs ─── --}}
    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-1 grid grid-cols-2 gap-1 mb-4">
        <a href="{{ route('jobs.index', ['side' => 'hiring']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center justify-center gap-1.5
                  {{ $side === 'hiring' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            وظائف متاحة
            <span class="text-[10px] {{ $side === 'hiring' ? 'text-white/70' : 'text-ink-400' }}">{{ $counts['hiring'] }}</span>
        </a>
        <a href="{{ route('jobs.index', ['side' => 'seeking']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center justify-center gap-1.5
                  {{ $side === 'seeking' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            بدوّر على شغل
            <span class="text-[10px] {{ $side === 'seeking' ? 'text-white/70' : 'text-ink-400' }}">{{ $counts['seeking'] }}</span>
        </a>
    </div>

    {{-- ─── Post CTA ─── --}}
    @if(session('flash'))
        <div class="mb-3 rounded-2xl bg-mint-50 ring-1 ring-mint-500/30 p-3 text-[12px] font-bold text-mint-800 flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('flash') }}
        </div>
    @endif

    <a href="{{ Auth::check() ? route('jobs.create', ['side' => $side]) : route('login') }}"
       class="block mb-4 rounded-2xl p-4 ring-1 ring-mint-500/30 bg-mint-50 hover:ring-mint-500/50 transition">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-mint-500 text-white grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">
                    @if($side === 'hiring')
                        محتاج تعيّن في نشاطك؟
                    @else
                        دوّر على شغل قربك
                    @endif
                </div>
                <div class="text-[11px] text-ink-500 mt-0.5">
                    @if($side === 'hiring')
                        أعلن عن الوظيفة هنا — مجاناً وبيوصل لأهل بنها
                    @else
                        انشر مهاراتك وخبراتك — أصحاب النشاطات بيشوفوا
                    @endif
                </div>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 text-mint-700 rtl:rotate-180">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
    </a>

    {{-- ─── List ─── --}}
    @if($items->isEmpty())
        <div class="card-light p-10 text-center">
            <span class="w-14 h-14 rounded-2xl bg-cream-100 text-ink-400 grid place-items-center mx-auto mb-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </span>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">
                @if($side === 'hiring')
                    مفيش وظائف متاحة دلوقتي
                @else
                    مفيش بوستات لسه
                @endif
            </h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto">
                @if($side === 'hiring')
                    ابدأ أنت ونشاطك يبقى أول واحد على لوحة وظائف بنها.
                @else
                    لو بتدور على شغل، انشر بوستك هنا وهيوصل لأصحاب النشاطات.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($items as $l)
                <a href="{{ route('marketplace.show', $l) }}"
                   class="block bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 hover:ring-coral-500/40 transition">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl bg-honey-100 text-honey-700 grid place-items-center shrink-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-extrabold text-ink-950 mb-0.5">{{ $l->title }}</h3>

                            @php
                                $m = $l->meta ?? [];
                                $empType = $m['employment_type'] ?? null;
                                $empLabel = $empType ? (\App\Models\Listing::EMPLOYMENT_TYPES[$empType] ?? null) : null;
                                $salaryMin = $m['salary_min'] ?? $l->price ?? null;
                                $salaryMax = $m['salary_max'] ?? null;
                                $employer = $m['employer_name'] ?? null;
                            @endphp

                            @if($employer)
                                <div class="text-[11px] font-bold text-ink-700 mb-1">{{ $employer }}</div>
                            @endif

                            <div class="flex items-center gap-1.5 flex-wrap mb-1.5">
                                @if($empLabel)
                                    <span class="px-2 py-0.5 rounded-full bg-coral-50 text-coral-700 text-[10px] font-extrabold">{{ $empLabel }}</span>
                                @endif
                                @if($salaryMin || $salaryMax)
                                    <span class="px-2 py-0.5 rounded-full bg-mint-100 text-mint-800 text-[10px] font-extrabold" dir="ltr">
                                        @if($salaryMin && $salaryMax)
                                            {{ number_format($salaryMin) }}–{{ number_format($salaryMax) }} ج
                                        @elseif($salaryMin)
                                            من {{ number_format($salaryMin) }} ج
                                        @else
                                            حتى {{ number_format($salaryMax) }} ج
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <div class="text-[11px] text-ink-500 inline-flex items-center gap-1.5 flex-wrap">
                                @if($l->zone)
                                    <span class="inline-flex items-center gap-0.5">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $l->zone->name }}
                                    </span>
                                    <span>·</span>
                                @endif
                                <span>{{ $l->created_at->diffForHumans(short: true) }}</span>
                            </div>
                            @if($l->description)
                                <p class="text-[12px] text-ink-500 mt-1.5 leading-snug line-clamp-2">{{ $l->description }}</p>
                            @endif
                        </div>
                        @if($l->contact_phone)
                            <a href="tel:{{ $l->contact_phone }}"
                               onclick="event.stopPropagation()"
                               class="w-9 h-9 rounded-full bg-ink-950 text-white grid place-items-center shrink-0 hover:bg-ink-800 transition"
                               aria-label="اتصل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
