@extends('layouts.app', ['title' => 'اكتشف · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Search bar --}}
    <form method="GET" action="{{ route('discover') }}" class="card-light p-2 mb-4 flex items-center gap-2">
        <span class="ps-3 text-ink-400">
            <x-icon name="flame" class="w-5 h-5"/>
        </span>
        <input type="text" name="q" value="{{ $q }}" autofocus
               placeholder="دوّر على بوست، كلمة، شخص…"
               class="flex-1 bg-transparent outline-0 px-2 py-2.5 text-ink-950 placeholder-ink-400 text-sm">
        @if($q !== '' || $category)
            <a href="{{ route('discover') }}" class="text-ink-400 hover:text-ink-950 px-3 text-sm">إلغاء</a>
        @endif
        <button class="btn-primary !py-2 !px-5 text-sm">دوّر</button>
    </form>

    {{-- Category chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-5">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('discover') }}"
               class="chip {{ ! $category ? 'chip-active' : '' }}">الكل</a>
            @foreach($categories as $key => $label)
                <a href="{{ route('discover', ['category' => $key]) }}"
                   class="chip {{ $category === $key ? 'chip-active' : '' }}">
                    {{ $label }}
                    @if(($categoryCounts[$key] ?? 0) > 0)
                        <span class="text-xs opacity-60">{{ $categoryCounts[$key] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Results (search/filter) --}}
    @if($results)
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-extrabold text-ink-950">
                @if($q !== '')
                    نتائج "{{ $q }}"
                @elseif($category)
                    {{ $categories[$category] ?? $category }}
                @endif
            </h3>
            <span class="text-ink-400 text-xs">{{ $results->total() }} نتيجة</span>
        </div>

        @forelse($results as $post)
            @include('partials.post-card', ['post' => $post])
        @empty
            <div class="card-light p-10 text-center">
                <div class="icon-tile mx-auto mb-3 text-coral-600 w-14 h-14">
                    <x-icon name="flame" class="w-6 h-6"/>
                </div>
                <h3 class="font-extrabold text-ink-950">مفيش نتيجة</h3>
                <p class="text-ink-500 text-sm mt-1">جرّب كلمة تانية أو category مختلف.</p>
            </div>
        @endforelse

        @if($results->hasPages())
            <div class="mt-6">{{ $results->links() }}</div>
        @endif

    @else
        {{-- Top this week --}}
        @if($topWeek->isNotEmpty())
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-extrabold text-ink-950 inline-flex items-center gap-2">
                    <x-icon name="flame" class="w-5 h-5 text-coral-600"/>
                    أهم بوستات الأسبوع
                </h3>
                <a href="{{ route('feed') }}" class="text-coral text-xs font-bold">شوف الكل</a>
            </div>

            @foreach($topWeek as $post)
                @include('partials.post-card', ['post' => $post])
            @endforeach
        @else
            <div class="card-light p-10 text-center">
                <div class="icon-tile mx-auto mb-3 text-coral-600 w-14 h-14">
                    <x-icon name="flame" class="w-6 h-6"/>
                </div>
                <h3 class="font-extrabold text-ink-950">مفيش بوستات في الأسبوع ده لسه</h3>
                <p class="text-ink-500 text-sm mt-1">كن أول واحد يبدأ.</p>
                <a href="{{ route('posts.create') }}" class="btn-primary mt-5">
                    ابدأ بوست
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
