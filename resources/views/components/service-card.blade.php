@props(['service'])
@php
    $service = (object) $service;
@endphp
<article class="group relative flex flex-col h-full min-h-[220px] sm:min-h-[240px] md:min-h-[260px] rounded-xl overflow-hidden border border-stone-200 shadow-[0_1px_3px_0_rgba(0,0,0,0.06),0_1px_2px_-1px_rgba(0,0,0,0.06)] hover:border-sky-200 hover:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.08),0_2px_4px_-2px_rgba(0,0,0,0.06)] transition-all duration-300">
    <a href="{{ route('services.show', $service) }}" class="absolute inset-0 z-0" aria-label="{{ $service->title }}"><span class="sr-only">{{ $service->title }}</span></a>
    <div class="relative flex-1 flex flex-col min-h-0">
        <div class="absolute inset-0">
            <img src="{{ $service->image }}" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.style.display='none'">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/95 via-stone-900/30 to-transparent"></div>
        </div>
        <div class="relative z-10 mt-auto p-4 sm:p-5 md:p-6 flex flex-col">
            @if(!empty($service->category))
                <p class="text-sky-300/90 text-xs font-medium mb-1.5">{{ $service->category->name }}</p>
            @endif
            <a href="{{ route('services.show', $service) }}" class="hover:no-underline">
                <h3 class="text-white font-semibold text-lg mb-2 group-hover:text-sky-300 transition-colors">{{ $service->title }}</h3>
            </a>
            <p class="text-stone-300 text-sm line-clamp-2 leading-relaxed mb-4">{{ $service->description }}</p>
            <span class="inline-flex items-center gap-2 text-sky-400 text-sm font-medium group-hover:gap-3 transition-all duration-200 w-fit pointer-events-none">
                Подробнее об услуге
                <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
            </span>
        </div>
    </div>
</article>
