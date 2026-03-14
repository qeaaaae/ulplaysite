@props(['item'])
@php
    $item = (object) $item;
@endphp
<x-ui.card hover class="group flex flex-col h-full">
    <a href="/news/{{ $item->slug }}" class="block flex-1 flex flex-col">
        <div class="aspect-video overflow-hidden bg-stone-50 flex-shrink-0">
            <img src="{{ $item->image }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-[1.02]" loading="lazy" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $item->id ?? 0 }}/600/400';">
        </div>
        <div class="p-4 sm:p-5 flex-1 flex flex-col">
            <p class="text-stone-400 text-xs mb-2">{{ $item->published_at }}</p>
            <h3 class="font-medium text-stone-800 text-[15px] line-clamp-2 mb-2 group-hover:text-sky-600 transition-colors leading-snug">{{ $item->title }}</h3>
            <p class="text-stone-500 text-sm line-clamp-2 flex-1">{{ $item->description }}</p>
            <span class="inline-flex items-center gap-1 mt-3 text-sky-600 text-sm font-medium group-hover:gap-2 transition-all duration-200">
                Читать
                <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
            </span>
        </div>
    </a>
</x-ui.card>
