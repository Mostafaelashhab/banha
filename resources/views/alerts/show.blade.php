@extends('layouts.app', ['title' => 'تنبيه · ' . ($alert->typeMeta()['label'] ?? '') . ' · بنهاوي'])

@php
    use App\Support\AnonSeed;
    $meta    = $alert->typeMeta();
    $author  = $alert->user;
    $color   = AnonSeed::avatarColor($author->username);
    $initial = AnonSeed::initial($author->username);
    $isMine  = auth()->check() && auth()->id() === $alert->user_id;
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('alerts.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">تنبيه</h1>
        <button type="button" data-share data-share-url="{{ route('alerts.show', $alert) }}"
                data-share-title="تنبيه على بنهاوي · {{ $meta['label'] }}"
                data-share-text="{{ \Illuminate\Support\Str::limit($alert->description, 120) }}"
                class="ms-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-coral-100 text-coral-700 text-xs font-bold hover:bg-coral-500 hover:text-white transition" aria-label="شير">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            شير
        </button>
    </div>

    {{-- Alert detail card --}}
    <div class="card-light p-5 mb-4 {{ $alert->is_verified ? 'ring-2 ring-mint-500/30' : '' }}">
        <div class="flex items-start gap-3">
            <span class="w-12 h-12 rounded-2xl pill-{{ $meta['tone'] }} grid place-items-center shrink-0">
                <x-icon :name="$meta['icon']" class="w-6 h-6"/>
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center flex-wrap gap-1.5 mb-1">
                    <span class="text-base font-extrabold text-ink-950">{{ $meta['label'] }}</span>
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
                <p class="text-base text-ink-950 leading-relaxed mt-2">{{ $alert->description }}</p>

                <div class="flex flex-wrap items-center gap-3 mt-3 text-[11px] text-ink-400">
                    <span>{{ $author->username }}</span>
                    <span>·</span>
                    <span>من {{ $alert->created_at->diffForHumans(short: true) }}</span>
                    @if($alert->expires_at)
                        <span>·</span>
                        <span>ينتهي {{ $alert->expires_at->diffForHumans(short: true) }}</span>
                    @endif
                    <span>·</span>
                    <span class="text-mint-700 font-bold">{{ $alert->confirmations }} تأكيد</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 mt-4 pt-4 border-t border-ink-950/5">
            @if(! $isMine && ! $myConfirmed)
                <form method="POST" action="{{ route('alerts.confirm', $alert) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-cream-100 hover:bg-mint-100 hover:text-mint-700 text-ink-500 transition text-sm font-bold">
                        <x-icon name="check" class="w-4 h-4"/>
                        أكّد التنبيه
                    </button>
                </form>
            @elseif($myConfirmed)
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-mint-700">
                    <x-icon name="check" class="w-4 h-4"/>
                    أكّدت ده
                </span>
            @endif

            @if($isMine && ! $alert->is_resolved)
                <form method="POST" action="{{ route('alerts.resolve', $alert) }}" class="ms-auto"
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

    {{-- Emergency hotlines --}}
    @if(! empty($hotlines))
        <div class="card-light p-4 mb-4 border-2 border-blush-500/15 bg-blush-500/5">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <span class="w-7 h-7 rounded-full pill-blush grid place-items-center">
                    <x-icon name="bolt" class="w-3.5 h-3.5"/>
                </span>
                أرقام طوارئ مفيدة
            </h3>
            <div class="grid grid-cols-2 gap-2">
                @foreach($hotlines as $h)
                    <a href="tel:{{ $h['number'] }}"
                       class="flex items-center gap-2.5 bg-white rounded-2xl p-3 border border-ink-950/8 hover:border-coral-500/40 transition">
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
        </div>
    @endif

    {{-- Related --}}
    @if($related->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-3">تنبيهات تانية في {{ $alert->zone->name ?? 'الحي' }}</h3>
        <div class="space-y-2">
            @foreach($related as $r)
                @php $rmeta = $r->typeMeta(); @endphp
                <a href="{{ route('alerts.show', $r) }}" class="card-light p-3 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition">
                    <span class="w-10 h-10 rounded-xl pill-{{ $rmeta['tone'] }} grid place-items-center shrink-0">
                        <x-icon :name="$rmeta['icon']" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-ink-950">{{ $rmeta['label'] }}</div>
                        <div class="text-[11px] text-ink-500 truncate">{{ \Illuminate\Support\Str::limit($r->description, 80) }}</div>
                    </div>
                    <span class="text-[10px] text-ink-400 shrink-0">{{ $r->created_at->diffForHumans(short: true) }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
