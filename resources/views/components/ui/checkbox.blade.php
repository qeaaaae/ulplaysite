@props([
    'label' => null,
    'error' => null,
])

<div class="{{ $attributes->get('class') }}">
    <label class="flex items-center gap-3 cursor-pointer">
        <input
            type="checkbox"
            {{ $attributes->except('class')->merge([
                'class' => 'w-4 h-4 rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30'
            ]) }}
        >
        @if($label)
            <span class="text-sm text-stone-600">{{ $label }}</span>
        @endif
    </label>
    @if($error)
        <p class="mt-1.5 text-sm text-rose-400">{{ $error }}</p>
    @endif
</div>
