@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'options' => [],
    'placeholder' => 'Выберите...',
])

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="block text-sm font-medium text-stone-700 mb-1.5">
            {{ $label }}
        </label>
    @endif
    <select
        {{ $attributes->except('class')->merge([
            'class' => 'w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 appearance-none bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%2378716c\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-9'
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
