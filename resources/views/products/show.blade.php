@extends('layouts.app')

@section('content')
    <div>
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
            ))" class="!mb-0 py-4" />

            @php
                $images = $product->images;
                $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
                $hasSimilar = ($similarProducts ?? collect())->isNotEmpty();
            @endphp

            <div class="@if($hasSimilar) lg:grid lg:grid-cols-[7fr_3fr] lg:gap-5 xl:gap-6 @endif">
                <div class="min-w-0">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-6 lg:gap-8">
                        <div>
                            @if($cover)
                                <div class="aspect-[4/3] md:aspect-square rounded-xl overflow-hidden bg-stone-50 ring-1 ring-stone-200/50">
                                    <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="product-{{ $product->id }}">
                                        <img src="{{ $cover->url }}" alt="{{ $product->title }}" class="w-full h-full object-cover cursor-zoom-in" onerror="this.onerror=null;this.style.display='none'">
                                    </a>
                                </div>
                            @endif

                            @if($thumbs->count() > 0)
                                <div class="mt-3 relative overflow-hidden">
                                    <div class="flex gap-2">
                                        @foreach($thumbs as $image)
                                            <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="product-{{ $product->id }}" class="block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                                                <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="absolute right-0 top-0 bottom-0 w-16 bg-gradient-to-r from-transparent to-white pointer-events-none" aria-hidden="true"></div>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <h1 class="text-2xl sm:text-3xl font-semibold text-stone-900 mb-2">{{ $product->title }}</h1>

                            @php
                                $avgRating = (float) ($product->reviews_avg_rating ?? 0);
                                $reviewsCount = (int) ($product->reviews_count ?? 0);
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

                            @if($product->description)
                                <p class="text-stone-600 leading-relaxed mb-4">{{ $product->description }}</p>
                            @endif

                            <div class="pt-4 border-t border-stone-200">
                                @if(!$product->in_stock)
                                    <span class="inline-block px-3 py-1.5 bg-stone-600 text-white text-sm font-medium rounded-lg mb-4">Нет в наличии</span>
                                @endif

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl bg-stone-50/80 border border-stone-200/80" data-purchase-block>
                                    <div class="flex items-baseline gap-2 flex-wrap">
                                        <span class="text-2xl sm:text-3xl font-bold text-stone-900">{{ number_format($product->price, 0, ',', ' ') }} ₽</span>
                                        @if($product->discount_percent)
                                            <x-ui.badge variant="discount" size="sm">−{{ $product->discount_percent }}%</x-ui.badge>
                                        @endif
                                    </div>
                                    @if($product->in_stock)
                                        @if(in_array($product->id, $cartProductIds ?? []))
                                            @if(auth()->check())
                                                <x-ui.button href="{{ route('cart.index') }}" variant="outline" size="lg" class="sm:shrink-0">
                                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                                    В корзине
                                                </x-ui.button>
                                            @else
                                                <x-ui.button type="button" variant="outline" size="lg" class="sm:shrink-0" @click="openAuthModal('login')">
                                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                                    В корзине
                                                </x-ui.button>
                                            @endif
                                        @else
                                            <form action="{{ route('cart.add-product', $product) }}" method="POST" data-ajax-cart-add data-cart-url="{{ route('cart.index') }}" data-product-id="{{ $product->id }}" class="sm:shrink-0">
                                                @csrf
                                                <x-ui.button variant="primary" size="lg" type="submit" class="cart-add-btn w-full sm:w-auto">
                                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                                    В корзину
                                                </x-ui.button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-reviews-block
                        :reviewable="$product"
                        :reviews="$reviews"
                        :can-review="$canReview ?? false"
                        store-route="reviews.store.product"
                        :store-route-param="$product"
                    />
                </div>

                @if($hasSimilar)
                    <aside class="lg:sticky lg:top-4 lg:self-start mt-10 lg:mt-0 pt-8 lg:pt-0 border-t lg:border-t-0 border-stone-200">
                        <x-ui.section-heading icon="heroicon-o-squares-2x2" class="mb-4">Похожие товары</x-ui.section-heading>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-1 lg:gap-4">
                            @foreach($similarProducts as $similar)
                                <div class="min-w-0">
                                    @include('components.product-card', ['product' => $similar, 'cartProductIds' => $cartProductIds ?? []])
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ $product->category ? route('products.index', ['category' => $product->category->slug]) : route('products.index') }}" class="mt-6 inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                            @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                            {{ $product->category ? $product->category->name : 'В каталог' }}
                        </a>
                    </aside>
                @endif
            </div>

            @if(!$hasSimilar)
                <footer class="mt-12 pt-8 border-t border-stone-200">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                        @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                        В каталог
                    </a>
                </footer>
            @endif
        </div>
    </div>
@endsection
