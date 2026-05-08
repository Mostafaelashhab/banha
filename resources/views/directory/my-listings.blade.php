@extends('layouts.app', ['title' => 'نشاطاتي · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-black text-ink-950 inline-flex items-center gap-2">
            <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center text-white">
                <x-icon name="bag" class="w-4 h-4"/>
            </span>
            نشاطاتي
        </h1>
        <a href="{{ route('directory.create') }}" class="btn-primary !py-2 !px-4 text-sm">
            <x-icon name="plus" class="w-4 h-4"/>
            ضيف
        </a>
    </div>

    @forelse($businesses as $b)
        @php
            $sm = $b->subTypeMeta();
            $cm = $b->categoryMeta();
        @endphp
        <div class="card-light p-4 mb-2">
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl grid place-items-center text-2xl shrink-0"
                      style="background: {{ $cm['color'] }}20; border: 1px solid {{ $cm['color'] }}50">
                    {{ $b->emoji ?: $sm['emoji'] }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-extrabold text-ink-950 text-sm truncate">{{ $b->name }}</h3>
                        @if($b->is_verified)
                            <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                <x-icon name="check" class="w-3 h-3"/> موثّق
                            </span>
                        @elseif(! $b->is_active)
                            <span class="pill-blush text-[10px] font-bold px-2 py-0.5 rounded-full">محذوف</span>
                        @else
                            <span class="bg-honey-400/20 text-honey-500 text-[10px] font-bold px-2 py-0.5 rounded-full">في انتظار التوثيق</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-ink-500">
                        {{ $sm['label'] }}
                        @if($b->zone) · {{ $b->zone->name }} @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 pt-3 border-t border-ink-950/5">
                <a href="{{ route('directory.show', $b) }}" class="text-xs font-bold text-ink-500 hover:text-ink-950 px-3 py-1.5">
                    عرض
                </a>
                <a href="{{ route('directory.edit', $b) }}" class="text-xs font-bold text-coral-600 hover:text-coral-700 px-3 py-1.5 ms-auto">
                    عدّل
                </a>
            </div>
        </div>
    @empty
        <div class="card-light p-10 text-center">
            <div class="text-4xl mb-3">🛠️</div>
            <h3 class="font-extrabold text-ink-950 mb-1">لسه ما ضفتش نشاط</h3>
            <p class="text-ink-500 text-sm mb-5">لو إنت صنايعي، طبيب، صاحب مطعم، أو بزنس — سجّل نشاطك مجاناً وخلّي البنهاوية يعرفوك.</p>
            <a href="{{ route('directory.create') }}" class="btn-primary">
                ضيف نشاطك دلوقتي
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
        </div>
    @endforelse
</div>
@endsection
