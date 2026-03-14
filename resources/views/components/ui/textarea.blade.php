@props([
    'label' => null,
    'error' => null,
    'hint' => null,
])

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="block text-sm font-medium text-stone-700 mb-1.5">
            {{ $label }}
        </label>
    @endif
    <textarea
        {{ $attributes->except('class')->merge([
            'class' => 'w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y min-h-[100px]'
        ]) }}
    >{{ $slot }}</textarea>
    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-stone-500">{{ $hint }}</p>
    @endif
</div>
