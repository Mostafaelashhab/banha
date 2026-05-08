@props([
    'user'   => null,    // App\Models\User OR null when anonymous
    'name'   => null,    // explicit display name (for anon seed)
    'anon'   => false,   // when true, NEVER show user's avatar_url
    'size'   => 'md',    // sm | md | lg | xl
    'ring'   => false,
])

@php
    use App\Support\AnonSeed;

    $sizes = [
        'sm' => ['box' => 'w-7 h-7',   'text' => 'text-xs',  'rounded' => 'rounded-full'],
        'md' => ['box' => 'w-10 h-10', 'text' => 'text-sm',  'rounded' => 'rounded-full'],
        'lg' => ['box' => 'w-12 h-12', 'text' => 'text-lg',  'rounded' => 'rounded-2xl'],
        'xl' => ['box' => 'w-20 h-20 md:w-24 md:h-24', 'text' => 'text-3xl md:text-4xl', 'rounded' => 'rounded-2xl'],
    ];
    $cfg = $sizes[$size] ?? $sizes['md'];

    $displayName = $name ?? ($anon ? 'مجهول' : ($user?->username ?? 'مستخدم'));
    $color       = AnonSeed::avatarColor($displayName);
    $initial     = AnonSeed::initial($displayName);
    $url         = ($anon || ! $user) ? null : $user->avatar_url;
    $ringClass   = $ring ? 'ring-4 ring-white/30' : '';
@endphp

@if($url)
    <img src="{{ $url }}" alt=""
         {{ $attributes->merge(['class' => $cfg['box'].' '.$cfg['rounded'].' object-cover shrink-0 '.$ringClass]) }}>
@else
    <span {{ $attributes->merge(['class' => $cfg['box'].' '.$cfg['rounded'].' grid place-items-center text-white font-bold '.$cfg['text'].' shrink-0 '.$ringClass]) }}
          style="background: {{ $color }}">{{ $initial }}</span>
@endif
