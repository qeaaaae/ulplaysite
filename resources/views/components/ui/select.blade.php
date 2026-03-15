@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'options' => [],
    'placeholder' => 'Выберите...',
])

<div {{ $attributes->only('class')->merge(['class' => 'form-field']) }}>
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="block text-sm font-medium text-stone-700 mb-1.5">
            {{ $label }}
        </label>
    @endif
    <select
        data-enhance="tom-select"
        {{ $attributes->except('class')->merge([
            'class' => 'w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150'
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ is_array($optionLabel) ? $optionLabel['label'] ?? $optionLabel[0] : $optionLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-stone-500">{{ $hint }}</p>
    @endif
</div>
