{{-- Banhawy x-input — canonical text input. See DESIGN-SYSTEM.md §4.4. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-input name="name" label="الاسم" required placeholder="مثلاً: مطعم النيل"/> --}}
{{--   <x-input name="phone" type="tel" dir="ltr" label="رقم التليفون" :value="old('phone')" :error="$errors->first('phone')" helper="هنبعت كود تأكيد على واتساب"/> --}}
{{--   <x-input name="price" type="number" min="0" step="0.5" label="السعر" suffix="ج.م"/> --}}
{{--   <x-input name="search" icon="search" placeholder="ابحث عن مطعم…"/> --}}
{{-- The "name" prop is required. Other input attrs (min, max, maxlength, pattern, autocomplete, dir) pass through. --}}
@props([
    'name',                  // required
    'label'   => null,
    'type'    => 'text',
    'value'   => null,       // falls back to old($name)
    'placeholder' => null,
    'error'   => null,
    'helper'  => null,
    'icon'    => null,       // leading icon (x-icon name)
    'suffix'  => null,       // trailing static text (e.g. "ج.م")
    'required' => false,
    'disabled' => false,
    'id'      => null,
])

@php
    $id     = $id ?? $name;
    $val    = $value ?? old($name);
    $hasErr = (bool) $error;

    $inputBase = 'w-full bg-cream-50 rounded-xl text-ink-950 placeholder-ink-400 outline-0 border transition text-sm';
    $padding   = 'py-3';
    if ($icon)   { $padding .= ' ps-10 pe-4'; }
    elseif ($suffix) { $padding .= ' ps-4 pe-12'; }
    else { $padding .= ' px-4'; }

    $borderState = $hasErr
        ? 'border-blush-500 focus:border-blush-500 focus:ring-4 focus:ring-blush-500/10 focus:bg-white'
        : 'border-ink-950/8 focus:border-coral-500 focus:ring-4 focus:ring-coral-500/10 focus:bg-white';

    $disabledClass = $disabled ? 'bg-cream-200 text-ink-400 cursor-not-allowed' : '';

    $classes = trim("$inputBase $padding $borderState $disabledClass");
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-extrabold text-ink-700">
            {{ $label }}
            @if($required)<span class="text-blush-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="absolute start-3 top-1/2 -translate-y-1/2 text-ink-400 pointer-events-none">
                <x-icon :name="$icon" class="w-4 h-4"/>
            </span>
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if($val !== null) value="{{ $val }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @disabled($disabled)
            @if($hasErr) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge(['class' => $classes]) }}
        >

        @if($suffix)
            <span class="absolute end-3 top-1/2 -translate-y-1/2 text-[10px] font-extrabold text-ink-500 pointer-events-none">
                {{ $suffix }}
            </span>
        @endif
    </div>

    @if($hasErr)
        <p id="{{ $id }}-error" class="text-[11px] font-bold text-blush-500">{{ $error }}</p>
    @elseif($helper)
        <p class="text-[11px] text-ink-500">{{ $helper }}</p>
    @endif
</div>
