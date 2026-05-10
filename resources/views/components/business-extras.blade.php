@props([
    'subType' => null,
    'values'  => [],
])

@php
    /**
     * Renders ALL extra-field blocks but hides the ones that don't match
     * the currently picked sub_type (data-show-for attribute on each block).
     * A small JS snippet at the end watches the sub_type radios and toggles
     * visibility — so when the owner switches type, the form updates live.
     */
    $allFields = \App\Models\Business::EXTRA_FIELDS;
    $currentCat = \App\Models\Business::SUB_TYPES[$subType]['category'] ?? null;
@endphp

<div data-extras-wrap class="space-y-3">

    {{-- Header --}}
    <div class="flex items-center gap-2 pt-2">
        <span class="w-7 h-7 rounded-lg bg-coral-100 text-coral-600 grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>
            </svg>
        </span>
        <h3 class="text-sm font-extrabold text-ink-950">تفاصيل خاصة بنوع النشاط</h3>
        <span class="text-[10px] font-bold text-ink-400">اختياري</span>
    </div>
    <p class="text-[11px] text-ink-500 -mt-1.5" data-extras-empty>
        الحقول هنا بتتغيّر حسب نوع النشاط اللي اخترته فوق.
    </p>

    @foreach($allFields as $key => $def)
        @php
            $applies   = $def['applies_to'] ?? [];
            $showFor   = implode(',', $applies);
            $val       = $values[$key] ?? old('extra.'.$key);
            $matchNow  = (bool) ($currentCat && in_array($currentCat, $applies, true)) || in_array($subType, $applies, true);
        @endphp
        <div data-extra-field data-show-for="{{ $showFor }}" class="{{ $matchNow ? '' : 'hidden' }}">
            @if($def['type'] === 'text')
                <label class="text-xs font-bold text-ink-500 mb-1 block">{{ $def['label'] }}</label>
                <input type="text" name="extra[{{ $key }}]" maxlength="200"
                       value="{{ $val }}"
                       placeholder="{{ $def['placeholder'] ?? '' }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">

            @elseif($def['type'] === 'number')
                <label class="text-xs font-bold text-ink-500 mb-1 block">{{ $def['label'] }}</label>
                <input type="number" name="extra[{{ $key }}]" min="0" max="9999"
                       value="{{ $val }}"
                       placeholder="{{ $def['placeholder'] ?? '' }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">

            @elseif($def['type'] === 'select')
                <label class="text-xs font-bold text-ink-500 mb-1 block">{{ $def['label'] }}</label>
                <select name="extra[{{ $key }}]"
                        class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">
                    <option value="">— اختار —</option>
                    @foreach($def['options'] ?? [] as $optKey => $optLabel)
                        <option value="{{ $optKey }}" {{ (string) $val === (string) $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                </select>

            @elseif($def['type'] === 'checkbox')
                <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
                    {{-- hidden input to send "0" when unchecked, so we always know the user's intent --}}
                    <input type="hidden" name="extra[{{ $key }}]" value="0">
                    <input type="checkbox" name="extra[{{ $key }}]" value="1" {{ $val ? 'checked' : '' }} class="sr-only peer">
                    <span class="w-11 h-6 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                        <span class="absolute top-0.5 start-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.1rem] rtl:peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-bold text-ink-950">{{ $def['label'] }}</span>
                </label>
            @endif
        </div>
    @endforeach
</div>

@once
@push('scripts')
<script>
    // Reactive extras: watch sub_type input/radios and toggle which fields show.
    (function () {
        function readSubType() {
            // Works for both: radio group (legacy) and a single hidden input (new picker)
            const checked = document.querySelector('input[name="sub_type"]:checked');
            if (checked) return checked.value;
            const hidden = document.querySelector('input[name="sub_type"]');
            return hidden ? hidden.value : '';
        }
        function update() {
            const subType = readSubType();
            const wrap = document.querySelector('[data-extras-wrap]');
            if (!wrap) return;
            const subCat = (window.__BIZ_SUB_TO_CAT__ || {})[subType] || '';
            let anyShown = false;

            wrap.querySelectorAll('[data-extra-field]').forEach(div => {
                const targets = (div.dataset.showFor || '').split(',').map(s => s.trim()).filter(Boolean);
                const match = targets.includes(subCat) || targets.includes(subType);
                div.classList.toggle('hidden', !match);
                if (match) anyShown = true;
            });

            // Hide the hint once at least one field appears
            const hint = wrap.querySelector('[data-extras-empty]');
            if (hint) hint.classList.toggle('hidden', anyShown);
        }
        document.addEventListener('change', (e) => {
            if (e.target?.matches('input[name="sub_type"]')) update();
        });
        // Also listen for explicit "type changed" events the picker dispatches
        document.addEventListener('biz-type-changed', update);
        document.addEventListener('DOMContentLoaded', update);
    })();
</script>
@endpush
@endonce
