@props(['hover' => false])

@php
    $base = 'rounded-xl bg-white border border-stone-200 overflow-hidden transition-all duration-200';
    $hoverClass = $hover ? 'hover:border-stone-300 hover:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.08),0_2px_4px_-2px_rgba(0,0,0,0.06)]' : '';
@endphp

<div {{ $attributes->merge(['class' => $base . ' shadow-[0_1px_3px_0_rgba(0,0,0,0.06),0_1px_2px_-1px_rgba(0,0,0,0.06)] ' . $hoverClass]) }}>
    {{ $slot }}
</div>
