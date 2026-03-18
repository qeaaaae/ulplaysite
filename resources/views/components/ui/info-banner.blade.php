@props(['class' => 'mb-6'])

<p {{ $attributes->merge(['class' => 'p-4 rounded-xl border border-stone-200 bg-white text-stone-600 text-sm flex items-center gap-2 ' . $class]) }}>
    <span class="shrink-0 text-sky-600" aria-hidden="true">@svg('heroicon-o-information-circle', 'w-5 h-5')</span>
    <span>{{ $slot }}</span>
</p>
