@props([
    'user'    => null,    // App\Models\User OR null when anonymous
    'name'    => null,    // explicit display name (for anon seed)
    'anon'    => false,   // when true, NEVER show avatar_url or tier ring
    'size'    => 'md',    // sm | md | lg | xl
    'ring'    => false,
    'showTier'=> true,    // show silver/gold ring when not anonymous
    'showOnline' => true, // show green dot if user is online
])

@php
    use App\Support\AnonSeed;

    $sizes = [
        'sm' => ['box' => 'w-7 h-7',   'text' => 'text-xs',  'rounded' => 'rounded-full', 'dot' => 'w-2 h-2'],
        'md' => ['box' => 'w-10 h-10', 'text' => 'text-sm',  'rounded' => 'rounded-full', 'dot' => 'w-2.5 h-2.5'],
        'lg' => ['box' => 'w-12 h-12', 'text' => 'text-lg',  'rounded' => 'rounded-2xl',  'dot' => 'w-3 h-3'],
        'xl' => ['box' => 'w-20 h-20 md:w-24 md:h-24', 'text' => 'text-3xl md:text-4xl', 'rounded' => 'rounded-2xl', 'dot' => 'w-4 h-4'],
    ];
    $cfg = $sizes[$size] ?? $sizes['md'];

    $displayName = $name ?? ($anon ? 'مجهول' : ($user?->username ?? 'مستخدم'));
    $color       = AnonSeed::avatarColor($displayName);
    $initial     = AnonSeed::initial($displayName);
    $url         = ($anon || ! $user) ? null : $user->avatar_url;

    $tier        = ($anon || ! $user || ! $showTier) ? null : ($user->verification_tier ?? null);
    $tierClass   = match ($tier) {
        'gold'   => 'ring-tier-gold',
        'silver' => 'ring-tier-silver',
        default  => '',
    };
    $ringClass   = $ring ? 'ring-4 ring-white/30' : '';
    $isOnline    = ! $anon && $showOnline && $user && method_exists($user, 'isOnline') && $user->isOnline();
@endphp

<span class="relative inline-block shrink-0">
    @if($url)
        <img src="{{ $url }}" alt=""
             {{ $attributes->merge(['class' => $cfg['box'].' '.$cfg['rounded'].' object-cover '.$ringClass.' '.$tierClass]) }}>
    @else
        <span {{ $attributes->merge(['class' => $cfg['box'].' '.$cfg['rounded'].' grid place-items-center text-white font-bold '.$cfg['text'].' '.$ringClass.' '.$tierClass]) }}
              style="background: {{ $color }}">{{ $initial }}</span>
    @endif
    @if($isOnline)
        <span class="absolute bottom-0 end-0 {{ $cfg['dot'] }} rounded-full bg-mint-500 ring-2 ring-white" aria-label="أونلاين"></span>
    @endif
</span>
