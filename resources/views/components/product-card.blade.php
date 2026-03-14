@props(['product'])
@php
    $product = (object) $product;
    $hasDiscount = ($discount = $product->discount_percent ?? null) !== null;
@endphp
<article class="group flex flex-col h-full bg-white rounded-lg border border-stone-200 overflow-hidden shadow-sm hover:border-stone-300 hover:shadow-md transition-all duration-200">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-50">
        <a href="/products/{{ $product->slug }}" class="block absolute inset-0 z-0">
            <img src="{{ $product->image }}" alt="{{ $product->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $product->id ?? 0 }}/400/400';">
            <div class="absolute top-2 left-2 flex flex-wrap gap-1.5">
                @if(!($product->in_stock ?? true))
                    <span class="px-2 py-0.5 bg-stone-600 text-white text-[10px] font-medium rounded">Нет в наличии</span>
                @endif
            </div>
        </a>
        <button type="button" class="absolute top-2 right-2 z-10 flex items-center justify-center w-9 h-9 rounded-full bg-white/90 shadow-sm text-stone-500 hover:text-rose-500 hover:bg-white transition-all duration-200 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-sky-500/50 touch-manipulation cursor-pointer" title="Добавить в избранное" onclick="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Добавлено в избранное' } }));">
            @svg('heroicon-o-heart', 'w-5 h-5')
        </button>
    </div>
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <a href="/products/{{ $product->slug }}" class="block flex-1 group/title">
            <h3 class="font-medium text-stone-800 text-[15px] line-clamp-1 mb-2 group-hover/title:text-sky-600 transition-colors leading-snug">{{ $product->title }}</h3>
            @if(!empty($product->description))
                <p class="text-stone-500 text-[13px] line-clamp-2 leading-relaxed mb-3">{{ $product->description }}</p>
            @endif
            <div class="flex items-baseline gap-2 flex-wrap mt-auto">
                <span class="text-xl font-bold tabular-nums text-stone-900 tracking-tight">{{ number_format($product->price, 0, ',', ' ') }} ₽</span>
                @if($hasDiscount)
                    <x-ui.badge variant="discount" size="sm">−{{ $discount }}%</x-ui.badge>
                @endif
            </div>
        </a>
        <div class="mt-5 flex gap-2">
            <x-ui.button variant="primary" size="sm" class="flex-1 justify-center shadow-sm hover:shadow-md transition-shadow" type="button" onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Товар добавлен в корзину' } }));">
                @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                В корзину
            </x-ui.button>
            <a href="/products/{{ $product->slug }}" class="flex items-center justify-center w-10 h-9 rounded-md border border-stone-300 text-stone-600 hover:bg-stone-50 hover:border-stone-400 shrink-0 transition-colors cursor-pointer" title="Подробнее">
                @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4')
            </a>
        </div>
    </div>
</article>
