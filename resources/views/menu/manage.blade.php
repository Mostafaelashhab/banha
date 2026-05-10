@extends('layouts.app', ['title' => 'إدارة المنيو · ' . $business->name])

@section('content')
@php
    $catCount  = $business->menuCategories->count();
    $itemCount = $business->menuCategories->sum(fn ($c) => $c->items->count())
                + ($looseItems?->count() ?? 0);
    $availableCount = $business->menuCategories->sum(fn ($c) => $c->items->where('is_available', true)->count())
                    + ($looseItems?->where('is_available', true)->count() ?? 0);
    $menuUrl = route('menu.public', $business);
@endphp

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-base font-extrabold text-ink-950 truncate">{{ $business->name }}</h1>
            <p class="text-[11px] text-ink-500">{{ $catCount }} قسم · {{ $itemCount }} صنف · {{ $availableCount }} متوفر</p>
        </div>
        <a href="{{ $menuUrl }}" target="_blank" class="text-[11px] font-bold px-2.5 py-1.5 rounded-full bg-mint-100 text-mint-700 hover:bg-mint-500 hover:text-white transition inline-flex items-center gap-1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            معاينة
        </a>
    </div>

    {{-- QR card --}}
    <div class="card-light p-4 mb-4 bg-gradient-to-br from-coral-100/60 to-honey-100/60 border-coral-500/15">
        <div class="flex items-start gap-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=8&color=0B0B0C&bgcolor=FFFFFF&data={{ urlencode($menuUrl) }}"
                 alt="QR" class="w-20 h-20 rounded-2xl bg-white p-1.5 shrink-0 shadow-sm">
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-1.5 mb-0.5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3.5 h-3.5 text-coral-600">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/>
                        <line x1="18" y1="14" x2="18" y2="18"/><line x1="14" y1="18" x2="21" y2="18"/>
                    </svg>
                    QR للمنيو
                </h3>
                <p class="text-[11px] text-ink-500 leading-relaxed">طبعه وحطّه على الترابيزة. أي زبون يصوّره يفتحله المنيو.</p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=20&data={{ urlencode($menuUrl) }}"
                       download="qr-{{ $business->id }}.png" target="_blank"
                       class="inline-flex items-center gap-1 bg-coral-500 hover:bg-coral-600 text-white text-[11px] font-extrabold rounded-full px-3 py-1.5 transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        نزّل QR
                    </a>
                    <button type="button" data-share data-share-url="{{ $menuUrl }}"
                            data-share-title="منيو {{ $business->name }}"
                            class="inline-flex items-center gap-1 bg-white border border-ink-950/8 text-ink-950 text-[11px] font-extrabold rounded-full px-3 py-1.5 hover:bg-cream-100 transition">
                        <x-icon name="share" class="w-3 h-3"/> شارك
                    </button>
                    <a href="{{ $menuUrl }}" target="_blank"
                       class="inline-flex items-center gap-1 bg-white border border-ink-950/8 text-ink-500 hover:text-ink-950 text-[11px] font-bold rounded-full px-3 py-1.5 hover:bg-cream-100 transition truncate max-w-[160px]" dir="ltr">
                        {{ str_replace(['http://','https://'], '', $menuUrl) }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Add category --}}
    <form method="POST" action="{{ route('menu.category.store', $business) }}" class="card-light p-2 mb-3 flex gap-1.5">
        @csrf
        <input type="text" name="name" required maxlength="80"
               placeholder="قسم جديد — مثلاً: بيتزا، مشروبات، حلو…"
               class="flex-1 bg-cream-100 rounded-xl px-3 py-2.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">
        <button class="bg-coral-500 hover:bg-coral-600 text-white font-extrabold rounded-xl px-4 text-xs inline-flex items-center gap-1 transition shrink-0">
            <x-icon name="plus" class="w-3.5 h-3.5"/>
            <span class="hidden xs:inline">قسم</span>
        </button>
    </form>

    {{-- Categories with their items --}}
    @forelse($business->menuCategories as $cat)
        <div class="card-light p-4 mb-3" data-category="{{ $cat->id }}">
            {{-- Category header --}}
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-ink-950/8">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-coral-100 text-coral-600 grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </span>
                    <h3 class="font-extrabold text-ink-950 truncate">{{ $cat->name }}</h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cream-100 text-ink-500 shrink-0">{{ $cat->items->count() }}</span>
                </div>
                <form method="POST" action="{{ route('menu.category.destroy', $cat) }}"
                      data-confirm="حذف القسم {{ $cat->name }}؟"
                      data-confirm-body="الأصناف اللي فيه مش هتتمسح، هتفقد القسم بس."
                      data-confirm-tone="danger">
                    @csrf @method('DELETE')
                    <button class="w-7 h-7 rounded-full text-ink-400 hover:bg-blush-100 hover:text-blush-500 transition grid place-items-center" aria-label="احذف القسم">
                        <x-icon name="trash" class="w-3.5 h-3.5"/>
                    </button>
                </form>
            </div>

            {{-- Items --}}
            @if($cat->items->isNotEmpty())
                <div class="space-y-2 mb-3">
                    @foreach($cat->items as $item)
                        @include('menu.partials.item-row', ['item' => $item])
                    @endforeach
                </div>
            @else
                <p class="text-xs text-ink-400 text-center py-3 mb-2">مفيش أصناف في القسم لسه.</p>
            @endif

            {{-- Add item toggle --}}
            <button type="button" data-toggle-add-form
                    class="w-full text-center text-xs font-extrabold text-coral-600 hover:bg-coral-50 rounded-xl py-2 transition inline-flex items-center justify-center gap-1">
                <x-icon name="plus" class="w-3.5 h-3.5"/>
                <span data-toggle-label-open>أضف صنف للقسم</span>
                <span data-toggle-label-close class="hidden">إلغاء</span>
            </button>
            <div class="hidden mt-2" data-add-form-wrap>
                @include('menu.partials.add-item-form', ['business' => $business, 'categoryId' => $cat->id])
            </div>
        </div>
    @empty
        <div class="card-light p-6 text-center mb-3">
            <span class="w-12 h-12 mx-auto rounded-2xl bg-coral-100 text-coral-600 grid place-items-center mb-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </span>
            <p class="text-sm font-bold text-ink-950 mb-1">ابدأ بضيف قسم</p>
            <p class="text-xs text-ink-500 leading-relaxed">مثلاً: <b>بيتزا</b> أو <b>مشروبات</b> — بعدين تضيف الأصناف جوّاه.</p>
        </div>
    @endforelse

    {{-- Loose items (not in any category) --}}
    @if(isset($looseItems) && $looseItems->isNotEmpty())
        <div class="card-light p-4 mb-3 border-honey-500/20">
            <div class="flex items-center gap-2 mb-3 pb-3 border-b border-ink-950/8">
                <span class="w-7 h-7 rounded-lg bg-honey-100 text-honey-700 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-ink-950">أصناف بدون قسم</h3>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cream-100 text-ink-500">{{ $looseItems->count() }}</span>
            </div>
            <div class="space-y-2">
                @foreach($looseItems as $item)
                    @include('menu.partials.item-row', ['item' => $item])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Add item without category --}}
    <div class="card-light p-4">
        <button type="button" data-toggle-add-form
                class="w-full text-center text-sm font-extrabold text-coral-600 hover:bg-coral-50 rounded-xl py-2 transition inline-flex items-center justify-center gap-1.5">
            <x-icon name="plus" class="w-4 h-4"/>
            <span data-toggle-label-open>أضف صنف من غير قسم</span>
            <span data-toggle-label-close class="hidden">إلغاء</span>
        </button>
        <div class="hidden mt-3" data-add-form-wrap>
            @include('menu.partials.add-item-form', ['business' => $business, 'categoryId' => null])
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-toggle-add-form]').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrap = btn.parentElement.querySelector('[data-add-form-wrap]');
            const open = btn.querySelector('[data-toggle-label-open]');
            const close = btn.querySelector('[data-toggle-label-close]');
            const isHidden = wrap.classList.toggle('hidden');
            open.classList.toggle('hidden', !isHidden);
            close.classList.toggle('hidden', isHidden);
            if (!isHidden) {
                wrap.querySelector('input[name="name"]')?.focus();
            }
        });
    });
</script>
@endsection
