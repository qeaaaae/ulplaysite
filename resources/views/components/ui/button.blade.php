@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white disabled:opacity-50 disabled:cursor-not-allowed rounded-lg cursor-pointer';
    $variants = [
        'primary' => 'bg-sky-600 text-white shadow-sm hover:bg-sky-700 hover:shadow-md focus:ring-sky-500',
        'secondary' => 'bg-stone-100 text-stone-800 hover:bg-stone-200 focus:ring-stone-300',
        'outline' => 'border border-stone-300 bg-white text-stone-800 shadow-sm hover:border-sky-400 hover:text-sky-700 hover:bg-sky-50/90 focus:ring-sky-500/30 focus:border-sky-400',
        'subtle' => 'text-sky-600 hover:bg-sky-50 focus:ring-sky-500/30',
        'ghost' => 'text-stone-600 hover:bg-stone-100 focus:ring-stone-300',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500',
    ];
    $sizes = [
        'sm' => 'px-3.5 py-2 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-2.5 text-sm',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
