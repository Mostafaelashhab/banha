@php
    /** @var \App\Models\Price $price */
@endphp

<a href="{{ route('prices.show', $price->product_id) }}" class="card-light p-0 mb-3 overflow-hidden block hover:-translate-y-0.5 hover:shadow-lg transition">
    {{-- Top banner --}}
    <div class="px-4 py-2 flex items-center justify-between text-xs font-bold pill-mint">
        <span class="inline-flex items-center gap-1.5">
            <x-icon name="tag" class="w-3.5 h-3.5"/>
            رادار الأسعار · {{ $price->zone?->name ?? '' }}
        </span>
        <span class="text-[10px] opacity-80">{{ $price->created_at->diffForHumans(short: true) }}</span>
    </div>

    <div class="p-4 flex items-center gap-4">
        <span class="w-14 h-14 rounded-2xl grid place-items-center text-3xl shrink-0 bg-mint-100">
            {{ $price->product?->emoji ?: '🛒' }}
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-base font-extrabold text-ink-950 truncate">{{ $price->product?->name ?? 'منتج' }}</div>
            <div class="text-[11px] text-ink-500 mt-0.5">
                @if($price->shop_name) {{ $price->shop_name }} · @endif
                <span class="text-coral-600 font-bold">{{ $price->user?->username }}</span> سجّل السعر
            </div>
        </div>
        <div class="text-end shrink-0">
            <div class="text-2xl font-black text-ink-950 leading-none">
                {{ number_format((float) $price->price, 2) }}
                <span class="text-sm text-ink-400 font-bold">ج</span>
            </div>
            <div class="text-[10px] text-ink-400 mt-1">{{ $price->product?->unit ?? '' }}</div>
        </div>
    </div>
</a>
