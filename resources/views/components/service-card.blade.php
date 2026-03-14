@props(['service'])
@php
    $service = (object) $service;
@endphp
<article class="group relative flex flex-col h-full min-h-[240px] sm:min-h-[260px] md:min-h-[280px] rounded-xl overflow-hidden border border-stone-200 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-300">
    <a href="/services/{{ $service->slug }}" class="absolute inset-0 z-10">
        <span class="sr-only">{{ $service->title }}</span>
    </a>
    <div class="relative flex-1 flex flex-col min-h-0">
        <div class="absolute inset-0">
            <img src="{{ $service->image }}" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $service->id ?? 0 }}/600/400';">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/95 via-stone-900/30 to-transparent"></div>
        </div>
        <div class="relative mt-auto p-4 sm:p-5 md:p-6 flex flex-col">
            <h3 class="text-white font-semibold text-lg mb-2 group-hover:text-sky-300 transition-colors">{{ $service->title }}</h3>
            <p class="text-stone-300 text-sm line-clamp-2 leading-relaxed mb-4">{{ $service->description }}</p>
            <span class="inline-flex items-center gap-2 text-sky-400 text-sm font-medium group-hover:gap-3 transition-all duration-200">
                Подробнее об услуге
                <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
            </span>
        </div>
    </div>
</article>
