@props([
    'label' => null,
    'labelIcon' => null,
    'error' => null,
    'hint' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'form-field']) }}>
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="{{ $labelIcon ? 'flex items-center gap-2' : 'block' }} text-sm font-medium text-stone-700 mb-1.5">
            @if($labelIcon)
                @svg($labelIcon, 'w-4 h-4 text-stone-400 shrink-0')
            @endif
            {{ $label }}
        </label>
    @endif
    <input
        {{ $attributes->except('class')->merge([
            'class' => 'w-full h-11 px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 box-border'
        ]) }}
    >
    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-stone-500">{{ $hint }}</p>
    @endif
</div>
