@props([
    'name' => 'phone',
    'label' => 'Телефон',
    'labelIcon' => null,
    'value' => '',
    'required' => false,
    'error' => null,
])

@php
    $inputId = $attributes->get('id') ?? 'phone-' . \Illuminate\Support\Str::random(8);
@endphp

@php
    $initialValue = old($name, $value ?? '');
    $digits = preg_replace('/\D/', '', $initialValue);
    if (str_starts_with($digits, '8')) {
        $digits = substr($digits, 1);
    } elseif (str_starts_with($digits, '7')) {
        $digits = substr($digits, 1);
    }
    $digits = substr($digits, 0, 10);
    $formatted = $digits ? '+7' . (strlen($digits) >= 1 ? ' (' . substr($digits, 0, 3) : '') . (strlen($digits) >= 4 ? ') ' . substr($digits, 3, 3) : '') . (strlen($digits) >= 7 ? '-' . substr($digits, 6, 2) : '') . (strlen($digits) >= 9 ? '-' . substr($digits, 8, 2) : '') : '';
@endphp

<div class="form-field{{ $error ? ' is-invalid' : '' }}" x-data="phoneInput({{ \Illuminate\Support\Js::from($formatted) }})" x-init="init()">
    @if($label)
        <label for="{{ $inputId }}" class="{{ $labelIcon ? 'flex items-center gap-2' : 'block' }} text-sm font-medium text-stone-700 mb-1.5">
            @if($labelIcon)
                @svg($labelIcon, 'w-4 h-4 text-sky-500 shrink-0')
            @endif
            {{ $label }}
        </label>
    @endif
    <div class="input-wrapper relative flex items-center h-11 bg-white border border-stone-300 rounded-md focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-500 transition-colors duration-150">
        <span class="pl-3 flex items-center shrink-0" aria-hidden="true">
            <svg viewBox="0 0 9 6" class="w-6 h-4 rounded-sm overflow-hidden shrink-0" role="img" aria-hidden="true">
                <rect width="9" height="2" y="0" fill="#fff"/>
                <rect width="9" height="2" y="2" fill="#0039a6"/>
                <rect width="9" height="2" y="4" fill="#d52b1e"/>
            </svg>
        </span>
        <input
            type="tel"
            name="{{ $name }}"
            id="{{ $inputId }}"
            autocomplete="tel"
            inputmode="numeric"
            @if($required) required @endif
            x-model="displayValue"
            x-on:input="onInput($event)"
            x-on:keydown="onKeydown($event)"
            x-on:paste="onPaste($event)"
            maxlength="18"
            placeholder="+7 (___) ___-__-__"
            class="flex-1 min-w-0 h-full px-3 pr-11 py-2.5 bg-transparent border-0 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-0 rounded-md"
        >
        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center w-5 h-5 pointer-events-none">
            <span class="invalid-feedback-icon hidden text-rose-500" aria-hidden="true">@svg('heroicon-o-exclamation-circle', 'w-5 h-5')</span>
            <div class="phone-status-icons">
                <template x-if="displayValue && !isValid">
                    <span class="text-rose-500" aria-hidden="true">@svg('heroicon-o-x-circle', 'w-5 h-5')</span>
                </template>
                <template x-if="displayValue && isValid">
                    <span class="text-emerald-500" aria-hidden="true">@svg('heroicon-o-check-circle', 'w-5 h-5')</span>
                </template>
            </div>
        </div>
    </div>
    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
</div>
