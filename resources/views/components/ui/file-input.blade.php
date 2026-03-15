@props([
    'label' => null,
    'labelIcon' => null,
    'error' => null,
    'hint' => null,
])

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="{{ $labelIcon ? 'flex items-center gap-2' : 'block' }} text-sm font-medium text-stone-700 mb-1.5">
            @if($labelIcon)
                @svg($labelIcon, 'w-4 h-4 text-stone-400 shrink-0')
            @endif
            {{ $label }}
        </label>
    @endif
    <input type="file"
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full h-11 text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:h-9 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 file:cursor-pointer cursor-pointer border border-stone-300 rounded-md bg-white pl-0 pr-3 py-2 focus-within:outline-none focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-500 box-border'
        ]) }}
    >
    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-stone-500">{{ $hint }}</p>
    @endif
</div>
