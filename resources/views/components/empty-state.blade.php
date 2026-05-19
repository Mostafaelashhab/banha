{{-- Banhawy x-empty-state — empty/error placeholder. See DESIGN-SYSTEM.md §7. --}}
{{-- Two tones: `default` (informational, blue disc) and `danger` (errors, red disc). --}}
{{-- Pass an optional CTA via the `cta` slot — usually a button or link. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-empty-state icon="search" title="لسه مفيش نتايج" hint="جرّب كلمة تانية"/> --}}
{{--   <x-empty-state icon="bell" title="مفيش تنبيهات"> --}}
{{--     <x-slot:cta><x-button>شوف اللي حواليك</x-button></x-slot:cta> --}}
{{--   </x-empty-state> --}}
{{--   <x-empty-state tone="danger" icon="bolt" title="السيرفر بيرد ببطء" hint="حاول تاني"/> --}}
@props([
    'icon'  => 'search',
    'title' => '',
    'hint'  => null,
    'tone'  => 'default',     // default | danger
])

@php
    $discClass = $tone === 'danger'
        ? 'bg-blush-100 text-blush-500'
        : 'bg-coral-50 text-coral-600';
@endphp

<div {{ $attributes->merge(['class' => 'card-light p-8 text-center']) }}>
    <span class="w-12 h-12 mx-auto rounded-2xl grid place-items-center mb-3 {{ $discClass }}">
        <x-icon :name="$icon" class="w-5 h-5"/>
    </span>
    <p class="text-sm font-extrabold text-ink-950 mb-1">{{ $title }}</p>
    @if($hint)
        <p class="text-xs text-ink-500 leading-relaxed">{{ $hint }}</p>
    @endif
    @isset($cta)
        <div class="mt-4 flex justify-center">{{ $cta }}</div>
    @endisset
</div>
