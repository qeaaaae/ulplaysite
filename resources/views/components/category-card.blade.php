@props(['category', 'featured' => false])
@php
    $category = (object) $category;
@endphp
<x-ui.card hover class="group overflow-hidden h-full">
    <a href="/products?category={{ $category->slug }}" class="block h-full">
        <div class="relative {{ $featured ? 'aspect-square min-h-[120px] sm:min-h-[140px] lg:aspect-auto lg:h-full lg:min-h-0' : 'aspect-square min-h-[120px] sm:min-h-[140px]' }} overflow-hidden bg-stone-50">
            <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" loading="lazy" onerror="this.onerror=null;this.style.display='none'">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent flex flex-col justify-end p-4 sm:p-4 md:p-5">
                <h3 class="font-semibold text-white {{ $featured ? 'text-base lg:text-xl' : 'text-base' }}">
                    @if(!empty($category->parent))
                        <span class="text-white/80 font-normal">{{ $category->parent->name }} · </span>
                    @endif
                    {{ $category->name }}
                </h3>
                @if(!empty($category->description))
                    <p class="text-white/90 text-sm mt-1 line-clamp-2">{{ $category->description }}</p>
                @endif
                <p class="text-white/80 text-sm mt-1">{{ $category->count ?? $category->products_count ?? 0 }} товаров</p>
                <span class="inline-flex items-center gap-1.5 mt-3 text-sky-300 text-sm font-medium group-hover:gap-2 transition-all duration-200">
                    Смотреть
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </span>
            </div>
        </div>
    </a>
</x-ui.card>
