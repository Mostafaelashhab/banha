@extends('layouts.app', ['title' => 'تنبيهات لحظية · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-black text-ink-950 inline-flex items-center gap-2">
                <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center text-white">
                    <x-icon name="bolt" class="w-4 h-4"/>
                </span>
                تنبيهات لحظية
            </h1>
            <p class="text-ink-500 text-sm mt-1">{{ $alerts->count() }} تنبيه شغّال</p>
        </div>
        <a href="{{ route('alerts.create') }}" class="btn-primary !py-2 !px-4 text-sm">
            <x-icon name="plus" class="w-4 h-4"/>
            بلّغ
        </a>
    </div>

    {{-- Type chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-4">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('alerts.index') }}"
               class="chip {{ ! $activeType ? 'chip-active' : '' }}">الكل</a>
            @foreach($types as $key => $meta)
                <a href="{{ route('alerts.index', ['type' => $key]) }}"
                   class="chip {{ $activeType === $key ? 'chip-active' : '' }}">
                    <x-icon :name="$meta['icon']" class="w-3.5 h-3.5"/>
                    {{ $meta['label'] }}
                    @if(($typeCounts[$key] ?? 0) > 0)
                        <span class="text-xs opacity-60">{{ $typeCounts[$key] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Emergency hotlines --}}
    @if(! empty($hotlines))
        <div class="card-light p-4 mb-4 border-2 border-blush-500/15 bg-blush-500/5">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <span class="w-7 h-7 rounded-full pill-blush grid place-items-center">
                    <x-icon name="bolt" class="w-3.5 h-3.5"/>
                </span>
                @if($activeType)
                    أرقام طوارئ {{ \App\Models\Alert::TYPES[$activeType]['label'] ?? '' }}
                @else
                    أرقام طوارئ مهمة
                @endif
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($hotlines as $h)
                    <a href="tel:{{ $h['number'] }}"
                       class="flex items-center gap-2.5 bg-white rounded-2xl p-3 border border-ink-950/8 hover:border-coral-500/40 hover:-translate-y-0.5 transition group">
                        <span class="w-9 h-9 rounded-xl pill-{{ $h['tone'] }} grid place-items-center shrink-0">
                            <x-icon :name="$h['icon']" class="w-4 h-4"/>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-ink-500">{{ $h['label'] }}</div>
                            <div class="text-base font-black text-ink-950 leading-none mt-0.5" dir="ltr">{{ $h['number'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
            <p class="text-[11px] text-ink-400 mt-3 text-center">اضغط الرقم للاتصال مباشرة</p>
        </div>
    @endif

    {{-- Alerts list --}}
    <div class="space-y-2.5">
        @forelse($alerts as $alert)
            @php
                $meta = $alert->typeMeta();
                $confirmed = in_array($alert->id, $myConfirms, true);
                $isMine    = $alert->user_id === auth()->id();
            @endphp
            <div class="card-light p-4 {{ $alert->is_verified ? 'ring-1 ring-mint-500/30' : '' }}">
                <div class="flex items-start gap-3">
                    <span class="w-11 h-11 rounded-2xl pill-{{ $meta['tone'] }} grid place-items-center shrink-0">
                        <x-icon :name="$meta['icon']" class="w-5 h-5"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center flex-wrap gap-1.5 mb-1">
                            <span class="text-sm font-extrabold text-ink-950">{{ $meta['label'] }}</span>
                            @if($alert->zone)
                                <span class="text-ink-400 text-xs">· {{ $alert->zone->name }}</span>
                            @endif
                            @if($alert->is_verified)
                                <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <x-icon name="check" class="w-3 h-3"/>
                                    موثّق
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-ink-950 leading-relaxed">{{ $alert->description }}</p>
                        <div class="text-[11px] text-ink-400 mt-2">
                            {{ $alert->user->username }} · من {{ $alert->created_at->diffForHumans(short: true) }}
                            @if($alert->expires_at)
                                · ينتهي {{ $alert->expires_at->diffForHumans(short: true) }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 mt-3 pt-3 border-t border-ink-950/5 text-sm">
                    <a href="{{ route('alerts.show', $alert) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-ink-500 hover:text-ink-950 hover:bg-cream-200 transition text-xs font-bold">
                        تفاصيل
                    </a>

                    @if(! $isMine && ! $confirmed)
                        <form method="POST" action="{{ route('alerts.confirm', $alert) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-cream-100 hover:bg-coral-100 hover:text-coral-700 text-ink-500 transition text-xs font-bold">
                                <x-icon name="check" class="w-3.5 h-3.5"/>
                                أكّد ({{ $alert->confirmations }})
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold {{ $confirmed ? 'text-mint-700' : 'text-ink-400' }}">
                            @if($confirmed) <x-icon name="check" class="w-3.5 h-3.5"/> أنت أكّدت @endif
                            <span class="text-ink-400">{{ $alert->confirmations }} تأكيد</span>
                        </span>
                    @endif

                    <button type="button"
                            data-share data-share-url="{{ route('alerts.show', $alert) }}"
                            data-share-title="تنبيه على بنهاوي · {{ $meta['label'] }}"
                            data-share-text="{{ \Illuminate\Support\Str::limit($alert->description, 120) }}"
                            class="ms-auto p-2 rounded-full text-ink-400 hover:text-coral-600 hover:bg-cream-200 transition" aria-label="شير">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </button>

                    @if($isMine)
                        <form method="POST" action="{{ route('alerts.resolve', $alert) }}"
                              data-confirm="تأشير التنبيه كمنتهي؟"
                              data-confirm-action="نعم"
                              data-confirm-tone="danger">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-ink-400 hover:text-blush-500 hover:bg-blush-100 transition text-xs font-bold">
                                <x-icon name="trash" class="w-3.5 h-3.5"/>
                                خلاص انتهى
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card-light p-10 text-center">
                <div class="icon-tile mx-auto mb-3 text-coral-600 w-14 h-14">
                    <x-icon name="bolt" class="w-6 h-6"/>
                </div>
                <h3 class="font-extrabold text-ink-950 mb-1">مفيش تنبيهات شغّالة</h3>
                <p class="text-ink-500 text-sm mb-5">الجو هادي في حيك. شفت حاجة؟ بلّغ.</p>
                <a href="{{ route('alerts.create') }}" class="btn-primary">
                    بلّغ عن حاجة
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
