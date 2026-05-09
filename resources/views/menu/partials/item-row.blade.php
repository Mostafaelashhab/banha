<div class="flex items-center gap-2 p-2 rounded-xl bg-cream-100 {{ ! $item->is_available ? 'opacity-50' : '' }}">
    @if($item->photo_url)
        <img src="{{ $item->photo_url }}" alt="" loading="lazy" class="w-12 h-12 rounded-lg object-cover shrink-0">
    @else
        <span class="w-12 h-12 rounded-lg bg-white border border-ink-950/5 grid place-items-center text-ink-300 shrink-0">🍽</span>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-ink-950 truncate">{{ $item->name }}</span>
            @if($item->price)
                <span class="text-xs font-extrabold text-coral-600 shrink-0">{{ number_format($item->price, 0) }} ج</span>
            @endif
        </div>
        @if($item->description)
            <p class="text-[11px] text-ink-500 truncate">{{ $item->description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-1 shrink-0">
        <form method="POST" action="{{ route('menu.item.toggle', $item) }}">
            @csrf
            <button class="w-7 h-7 rounded-full {{ $item->is_available ? 'bg-mint-500 text-white' : 'bg-ink-300 text-white' }} text-xs grid place-items-center" title="{{ $item->is_available ? 'متوفر' : 'مش متوفر' }}">
                {{ $item->is_available ? '✓' : '×' }}
            </button>
        </form>
        <form method="POST" action="{{ route('menu.item.destroy', $item) }}" data-confirm="حذف الصنف؟" data-confirm-tone="danger">
            @csrf @method('DELETE')
            <button class="w-7 h-7 rounded-full bg-blush-100 text-blush-500 hover:bg-blush-500 hover:text-white transition grid place-items-center" title="حذف">
                <x-icon name="trash" class="w-3.5 h-3.5"/>
            </button>
        </form>
    </div>
</div>
