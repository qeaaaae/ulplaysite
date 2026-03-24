@props(['product'])
@php
    $product = (object) $product;
    $hasDiscount = ($discount = $product->discount_percent ?? null) !== null;
    $inCart = in_array($product->id ?? 0, $cartProductIds ?? []);
@endphp
<article class="group flex flex-col h-full bg-white rounded-xl border border-stone-200 overflow-hidden shadow-[0_1px_3px_0_rgba(0,0,0,0.06),0_1px_2px_-1px_rgba(0,0,0,0.06)] hover:border-stone-300 hover:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.08),0_2px_4px_-2px_rgba(0,0,0,0.06)] transition-all duration-200">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-50">
        <a href="/products/{{ $product->slug }}" class="block absolute inset-0 z-0">
            <img src="{{ $product->image }}" alt="{{ $product->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.style.display='none'">
            <div class="absolute top-2 left-2 flex flex-wrap gap-1.5">
                @if(!($product->in_stock ?? true))
                    <span class="px-2 py-0.5 bg-stone-600 text-white text-[10px] font-medium rounded">Нет в наличии</span>
                @endif
            </div>
        </a>
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
            @php
                $avgRating = isset($product->reviews_avg_rating) ? (float) $product->reviews_avg_rating : 0;
                $reviewsCount = $product->reviews_count ?? 0;
            @endphp
            <div class="flex items-center gap-2 mt-1 flex-wrap" aria-label="Рейтинг: {{ $avgRating }} из 5">
                <span class="flex gap-0.5 text-3xl leading-none">
                    @for($i = 1; $i <= 5; $i++)
                        @php
                            $fill = $avgRating >= $i ? 100 : ($avgRating > $i - 1 ? (int) round(($avgRating - ($i - 1)) * 100) : 0);
                        @endphp
                        <span class="relative inline-block">
                            <span class="text-stone-300">★</span>
                            @if($fill > 0)
                                <span class="absolute left-0 top-0 h-full overflow-hidden text-sky-600" style="width: {{ $fill }}%">
                                    <span class="inline-block" style="width: {{ $fill < 100 ? round(10000 / $fill) : 100 }}%">★</span>
                                </span>
                            @endif
                        </span>
                    @endfor
                </span>
                @if($reviewsCount > 0)
                    <a href="{{ route('products.show', $product) }}#reviews" class="text-stone-500 text-sm hover:text-sky-600 transition-colors">{{ $reviewsCount }} @if($reviewsCount === 1)отзыв@elseif($reviewsCount >= 2 && $reviewsCount <= 4)отзыва@else отзывов @endif</a>
                @else
                    <span class="text-stone-400 text-sm">Нет отзывов</span>
                @endif
            </div>
        <div class="mt-4 flex gap-2">
            <div class="flex-1 min-w-0 flex">
                @if($inCart)
                    @if(auth()->check())
                        <x-ui.button href="{{ route('cart.index') }}" variant="outline" size="sm" class="w-full justify-center h-11">
                            @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                            В корзине
                        </x-ui.button>
                    @else
                        <x-ui.button type="button" variant="outline" size="sm" class="w-full justify-center h-11" @click="openAuthModal('login')">
                            @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                            В корзине
                        </x-ui.button>
                    @endif
                @else
                    <form action="{{ route('cart.add-product', $product) }}" method="POST" class="w-full cart-add-form" data-ajax-cart-add data-cart-url="{{ route('cart.index') }}" data-product-id="{{ $product->id ?? '' }}">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <x-ui.button variant="primary" size="sm" class="w-full justify-center shadow-sm hover:shadow-md transition-shadow cart-add-btn h-11" type="submit">
                            @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                            В корзину
                        </x-ui.button>
                    </form>
                @endif
            </div>
            <a href="/products/{{ $product->slug }}" class="flex items-center justify-center w-11 h-11 rounded-md border border-stone-300 text-stone-600 hover:bg-stone-50 hover:border-stone-400 shrink-0 transition-colors cursor-pointer" title="Подробнее">
                @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4')
            </a>
        </div>
    </div>
</article>
