@props(['hover' => false])

@php
    $base = 'rounded-lg bg-white border border-stone-200 overflow-hidden transition-colors duration-150';
    $hoverClass = $hover ? 'hover:border-stone-300' : '';
@endphp

<div {{ $attributes->merge(['class' => $base . ' ' . $hoverClass]) }}>
    {{ $slot }}
</div>
