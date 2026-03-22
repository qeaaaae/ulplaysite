@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            @php
                $categoryTrail = [];
                $category = $product->category;

                while ($category) {
                    $categoryTrail[] = [
                        'label' => $category->name,
                        'url' => route('products.index', ['category' => $category->slug]),
                    ];
                    $category = $category->parent;
                }

                $categoryTrail = array_reverse($categoryTrail);
            @endphp

            <x-ui.breadcrumbs :items="array_filter(array_merge(
                [
                    ['label' => 'Главная', 'url' => route('home')],
                    ['label' => 'Каталог', 'url' => route('products.index')],
                ],
                $categoryTrail,
                [
                    ['label' => $product->title, 'url' => null],
                ],
            ))" />

            @php
                $images = $product->images;
                $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <div>
                    @if($cover)
                        <div class="aspect-[4/3] lg:aspect-square rounded-xl overflow-hidden bg-stone-50 shadow-sm">
                            <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="product-{{ $product->id }}">
                                <img src="{{ $cover->url }}" alt="{{ $product->title }}" class="w-full h-full object-cover cursor-zoom-in" onerror="this.onerror=null;this.style.display='none'">
                            </a>
                        </div>
                    @endif

                    @if($thumbs->count() > 0)
                        <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                            @foreach($thumbs as $image)
                                <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="product-{{ $product->id }}" class="block w-20 h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                                    <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900 mt-2 mb-3">{{ $product->title }}</h1>
                    @php
                        $reviews = $reviews ?? $product->reviews;
                        $avgRating = $reviews->isEmpty() ? 0 : (float) $reviews->avg('rating');
                        $reviewsCount = $reviews->count();
                    @endphp
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-lg">
                            <span class="flex gap-0.5 text-xl text-sky-600 leading-none" aria-hidden="true">
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $fill = $avgRating >= $i ? 100 : ($avgRating > $i - 1 ? (int) round(($avgRating - ($i - 1)) * 100) : 0);
                                    @endphp
                                    <span class="relative inline-block">
                                        <span class="text-stone-200">★</span>
                                        @if($fill > 0)
                                            <span class="absolute left-0 top-0 h-full overflow-hidden text-sky-600" data-star-fill="{{ $fill }}">
                                                <span class="inline-block" data-inner-star-fill="{{ $fill < 100 ? round(10000 / $fill) : 100 }}">★</span>
                                            </span>
                                        @endif
                                    </span>
                                @endfor
                            </span>
                            <span class="font-semibold text-stone-900 tabular-nums">{{ number_format($avgRating, 1, ',', '') }}</span>
                        </div>
                        @if($reviewsCount > 0)
                            <a href="#reviews" class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-sky-600 transition-colors">
                                {{ $reviewsCount }} @if($reviewsCount === 1)отзыв@elseif($reviewsCount >= 2 && $reviewsCount <= 4)отзыва@else отзывов @endif
                                @svg('heroicon-o-chevron-down', 'w-4 h-4')
                            </a>
                        @else
                            <span class="text-stone-400 text-sm">Нет отзывов</span>
                        @endif
                    </div>
                    @if(!$product->in_stock)
                        <span class="inline-block px-3 py-1 bg-stone-600 text-white text-sm font-medium rounded mb-4">Нет в наличии</span>
                    @endif
                    @if($product->description)
                        <p class="text-stone-600 leading-relaxed mb-6">{{ $product->description }}</p>
                    @endif
                    <div class="flex items-baseline gap-3 mb-6">
                        <span class="text-2xl font-bold text-stone-900">{{ number_format($product->price, 0, ',', ' ') }} ₽</span>
                        @if($product->discount_percent)
                            <x-ui.badge variant="discount" size="sm">−{{ $product->discount_percent }}%</x-ui.badge>
                        @endif
                    </div>
                    @if($product->in_stock)
                        @if(in_array($product->id, $cartProductIds ?? []))
                            @if(auth()->check())
                                <x-ui.button href="{{ route('cart.index') }}" variant="outline" size="lg">
                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                    В корзине
                                </x-ui.button>
                            @else
                                <x-ui.button type="button" variant="outline" size="lg" @click="openAuthModal('login')">
                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                    В корзине
                                </x-ui.button>
                            @endif
                        @else
                            <form action="{{ route('cart.add-product', $product) }}" method="POST" data-ajax-cart-add data-cart-url="{{ route('cart.index') }}">
                                @csrf
                                <x-ui.button variant="primary" size="lg" type="submit" class="cart-add-btn">
                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                    В корзину
                                </x-ui.button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            @if(($similarProducts ?? collect())->isNotEmpty())
                <section class="mt-10 pt-8 border-t border-stone-200 overflow-hidden">
                    <x-ui.section-heading icon="heroicon-o-squares-2x2" class="mb-4">Похожие товары</x-ui.section-heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @foreach($similarProducts as $similar)
                            @include('components.product-card', ['product' => $similar, 'cartProductIds' => $cartProductIds ?? []])
                        @endforeach
                    </div>
                </section>
            @endif

            <x-reviews-block
                :reviewable="$product"
                :reviews="$reviews ?? $product->reviews"
                :can-review="$canReview ?? false"
                store-route="reviews.store.product"
                :store-route-param="$product"
            />
        </div>
    </div>
@endsection
