@props([
    'icon' => null,
    'class' => 'mb-3 md:mb-4',
    'tag' => 'h2',
    /** Отступ сверху, если заголовок сразу под border-t у родителя */
    'afterBorder' => false,
])

@php
    $tag = in_array($tag, ['h1', 'h2', 'h3'], true) ? $tag : 'h2';
    $afterBorderClass = $afterBorder ? 'pt-6 ' : '';
@endphp
<{{ $tag }} {{ $attributes->merge(['class' => $afterBorderClass . 'section-heading text-2xl flex items-center gap-2.5 ' . $class]) }}>
    @if($icon)
        <span class="text-sky-600 shrink-0">@svg($icon, 'w-6 h-6')</span>
    @endif
    {{ $slot }}
</{{ $tag }}>
