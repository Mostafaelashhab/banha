@php
    $L = $L ?? \App\Models\Business::menuLabels($item->business->category ?? 'services');
    $extraPhotos = is_array($item->photos) ? count($item->photos) : 0;
    $features    = is_array($item->features) ? $item->features : [];
@endphp

<div class="rounded-xl bg-cream-100 hover:bg-cream-200/50 transition {{ ! $item->is_available ? 'opacity-55' : '' }}">
    <div class="flex items-center gap-2.5 p-2">
        <div class="relative shrink-0">
            @if($item->photo_url)
                <img src="{{ $item->photo_url }}" alt="" loading="lazy" class="w-12 h-12 rounded-lg object-cover">
            @else
                <span class="w-12 h-12 rounded-lg bg-white border border-ink-950/8 grid place-items-center text-ink-300">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </span>
            @endif
            @if($extraPhotos > 0)
                <span class="absolute -top-1 -end-1 bg-coral-500 text-white text-[9px] font-extrabold rounded-full min-w-[18px] h-[18px] px-1 grid place-items-center border-2 border-cream-100"
                      title="{{ $extraPhotos }} صور إضافية">+{{ $extraPhotos }}</span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-ink-950 truncate">{{ $item->name }}</span>
                @if(! $item->is_available)
                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-ink-300 text-white shrink-0">مش متوفر</span>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-0.5">
                @if($item->price)
                    <span class="text-xs font-extrabold text-coral-600 shrink-0">{{ number_format($item->price, 0) }} ج.م</span>
                @endif
                @if($item->capacity)
                    <span class="text-[10px] font-bold text-ink-500 inline-flex items-center gap-0.5 shrink-0">
                        <x-icon name="user" class="w-3 h-3"/>{{ $item->capacity }}
                    </span>
                @endif
                @if($item->description)
                    <p class="text-[11px] text-ink-500 truncate">{{ $item->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <form method="POST" action="{{ route('menu.item.toggle', $item) }}">
                @csrf
                <button class="w-8 h-8 rounded-full {{ $item->is_available ? 'bg-mint-100 text-mint-700 hover:bg-mint-500 hover:text-white' : 'bg-ink-100 text-ink-400 hover:bg-ink-300 hover:text-white' }} transition grid place-items-center"
                        title="{{ $item->is_available ? 'متوفر — اضغط لتعطيل' : 'مش متوفر — اضغط لتفعيل' }}"
                        aria-label="{{ $item->is_available ? 'تعطيل' : 'تفعيل' }}">
                    @if($item->is_available)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    @endif
                </button>
            </form>
            <form method="POST" action="{{ route('menu.item.destroy', $item) }}"
                  data-confirm="حذف {{ $item->name }}؟" data-confirm-tone="danger">
                @csrf @method('DELETE')
                <button class="w-8 h-8 rounded-full bg-blush-100 text-blush-500 hover:bg-blush-500 hover:text-white transition grid place-items-center" aria-label="حذف">
                    <x-icon name="trash" class="w-3.5 h-3.5"/>
                </button>
            </form>
        </div>
    </div>

    @if(! empty($features))
        <div class="flex flex-wrap gap-1 px-2.5 pb-2">
            @foreach($features as $f)
                <span class="inline-flex items-center gap-1 bg-white text-ink-700 text-[10px] font-bold rounded-full px-2 py-0.5 border border-ink-950/8">
                    <x-icon name="{{ $f['icon'] ?? 'tag' }}" class="w-3 h-3 text-coral-600"/>
                    {{ $f['label'] ?? '' }}
                </span>
            @endforeach
        </div>
    @endif
</div>
