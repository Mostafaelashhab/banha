{{-- Banhawy x-empty-state — empty/error placeholder. See DESIGN-SYSTEM.md §7. --}}
{{-- Two tones: `default` (informational, blue disc) and `danger` (errors, red disc). --}}
{{-- Two sizes: `md` (compact, inside lists) and `lg` (hero, full-page empties). --}}
{{-- Pass an optional CTA via the `cta` slot — usually a button or link. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-empty-state icon="search" title="لسه مفيش نتايج" hint="جرّب كلمة تانية"/>      compact --}}
{{--   <x-empty-state size="lg" icon="heart" title="مفيش حاجة محفوظة" hint="..."/>     hero --}}
{{--   <x-empty-state icon="bell" title="مفيش تنبيهات"> --}}
{{--     <x-slot:cta><x-button>شوف اللي حواليك</x-button></x-slot:cta> --}}
{{--   </x-empty-state> --}}
{{--   <x-empty-state tone="danger" icon="bolt" title="السيرفر بيرد ببطء" hint="حاول تاني"/> --}}
@props([
    'icon'  => 'search',
    'title' => '',
    'hint'  => null,
    'tone'  => 'default',     /* default | danger */
    'size'  => 'md',          /* md (compact) | lg (hero) */
])

@php
    $discClass = $tone === 'danger'
        ? 'bg-blush-100 text-blush-500'
        : 'bg-coral-50 text-coral-600';

    [$padClass, $discBox, $discInner, $titleClass, $hintClass] = $size === 'lg'
        ? ['p-10', 'w-16 h-16 rounded-full', 'w-7 h-7', 'text-xl font-extrabold mb-1', 'text-sm leading-relaxed']
        : ['p-8',  'w-12 h-12 rounded-2xl', 'w-5 h-5', 'text-sm font-extrabold mb-1', 'text-xs leading-relaxed'];
@endphp

<div {{ $attributes->merge(['class' => "card-light $padClass text-center"]) }}>
    <span class="{{ $discBox }} mx-auto grid place-items-center mb-3 {{ $discClass }}">
        <x-icon :name="$icon" :class="$discInner"/>
    </span>
    <p class="{{ $titleClass }} text-ink-950">{{ $title }}</p>
    @if($hint)
        <p class="{{ $hintClass }} text-ink-500">{{ $hint }}</p>
    @endif
    @isset($cta)
        <div class="mt-4 flex justify-center">{{ $cta }}</div>
    @endisset
</div>
