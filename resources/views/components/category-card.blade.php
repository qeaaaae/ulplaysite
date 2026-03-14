@props(['category', 'featured' => false])
@php
    $category = (object) $category;
@endphp
<x-ui.card hover class="rounded-xl group overflow-hidden h-full">
    <a href="/products?category={{ $category->slug }}" class="block h-full">
        <div class="relative {{ $featured ? 'aspect-[4/3] md:aspect-[1] min-h-[180px] md:min-h-[240px] lg:min-h-[280px]' : 'aspect-square min-h-[120px] sm:min-h-[140px]' }} overflow-hidden bg-stone-50">
            <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.05]" loading="lazy" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $category->id ?? 0 }}/600/600';">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent flex flex-col justify-end p-4 sm:p-4 md:p-5">
                <h3 class="font-semibold text-white {{ $featured ? 'text-lg sm:text-xl' : 'text-base' }}">{{ $category->name }}</h3>
                @if(!empty($category->description))
                    <p class="text-white/90 text-sm mt-1 line-clamp-2">{{ $category->description }}</p>
                @endif
                <p class="text-white/80 text-sm mt-1">{{ $category->count ?? 0 }} товаров</p>
                <span class="inline-flex items-center gap-1.5 mt-3 text-sky-300 text-sm font-medium group-hover:gap-2 transition-all duration-200">
                    Смотреть
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </span>
            </div>
        </div>
    </a>
</x-ui.card>
