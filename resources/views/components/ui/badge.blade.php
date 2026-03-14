@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center font-medium';
    $variants = [
        'default' => 'bg-stone-100 text-stone-600',
        'primary' => 'bg-sky-50 text-sky-700',
        'success' => 'bg-emerald-50 text-emerald-700',
        'warning' => 'bg-amber-50 text-amber-700',
        'danger' => 'bg-rose-50 text-rose-700',
        'discount' => 'bg-sky-100 text-sky-700',
        'new' => 'bg-sky-50 text-sky-700',
    ];
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs rounded',
        'md' => 'px-2.5 py-1 text-xs rounded',
        'lg' => 'px-3 py-1.5 text-sm rounded',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
