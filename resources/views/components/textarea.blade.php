{{-- Banhawy x-textarea — multi-line input. See DESIGN-SYSTEM.md §4.4. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-textarea name="description" label="الوصف" rows="3" placeholder="كل التفاصيل…" :error="$errors->first('description')"/> --}}
{{--   <x-textarea name="bio" maxlength="500" counter helper="مختصر يصف نشاطك"/> --}}
{{-- Set `counter` to true to show a live "X / max" counter (requires maxlength). --}}
@props([
    'name',
    'label'   => null,
    'value'   => null,
    'placeholder' => null,
    'error'   => null,
    'helper'  => null,
    'rows'    => 3,
    'maxlength' => null,
    'counter' => false,
    'required' => false,
    'disabled' => false,
    'id'      => null,
])

@php
    $id     = $id ?? $name;
    $val    = $value ?? old($name);
    $hasErr = (bool) $error;
    $showCounter = $counter && $maxlength;

    $base = 'w-full bg-cream-50 rounded-xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border transition text-sm resize-none leading-relaxed';
    $borderState = $hasErr
        ? 'border-blush-500 focus:border-blush-500 focus:ring-4 focus:ring-blush-500/10 focus:bg-white'
        : 'border-ink-950/8 focus:border-coral-500 focus:ring-4 focus:ring-coral-500/10 focus:bg-white';
    $disabledClass = $disabled ? 'bg-cream-200 text-ink-400 cursor-not-allowed' : '';

    $classes = trim("$base $borderState $disabledClass");
@endphp

<div {{ $showCounter ? 'data-textarea-counter' : '' }} class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-extrabold text-ink-700">
            {{ $label }}
            @if($required)<span class="text-blush-500">*</span>@endif
        </label>
    @endif

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @disabled($disabled)
        @if($hasErr) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >{{ $val }}</textarea>

    <div class="flex items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            @if($hasErr)
                <p id="{{ $id }}-error" class="text-[11px] font-bold text-blush-500">{{ $error }}</p>
            @elseif($helper)
                <p class="text-[11px] text-ink-500">{{ $helper }}</p>
            @endif
        </div>
        @if($showCounter)
            <span class="text-[10px] text-ink-400 font-mono shrink-0" data-textarea-counter-display>0 / {{ $maxlength }}</span>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
// Lightweight char counter — runs once per page, hydrates every textarea with `data-textarea-counter`.
(function () {
    document.querySelectorAll('[data-textarea-counter]').forEach(wrap => {
        const ta = wrap.querySelector('textarea');
        const display = wrap.querySelector('[data-textarea-counter-display]');
        if (!ta || !display) return;
        const max = ta.maxLength;
        const update = () => { display.textContent = ta.value.length + ' / ' + max; };
        ta.addEventListener('input', update);
        update();
    });
})();
</script>
@endpush
@endonce
