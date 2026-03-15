@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <nav class="text-sm text-stone-500 mb-6">
                <a href="{{ route('home') }}" class="hover:text-sky-600">Главная</a>
                <span class="mx-2">/</span>
                <a href="{{ route('products.index') }}" class="hover:text-sky-600">Каталог</a>
                @if($product->category)
                    <span class="mx-2">/</span>
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-sky-600">{{ $product->category->name }}</a>
                @endif
                <span class="mx-2">/</span>
                <span class="text-stone-800">{{ $product->title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <div class="aspect-[4/3] lg:aspect-square rounded-xl overflow-hidden bg-stone-50 shadow-sm">
                    <img src="{{ $product->image ?: $product->image_path }}" alt="{{ $product->title }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $product->id }}/800/800';">
                </div>
                <div>
                    @if($product->category)
                        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-sky-600 text-sm font-medium hover:underline">{{ $product->category->name }}</a>
                    @endif
                    <h1 class="text-2xl font-semibold text-stone-900 mt-2 mb-4">{{ $product->title }}</h1>
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
                            <x-ui.button href="{{ route('cart.index') }}" variant="outline" size="lg">
                                @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                В корзине
                            </x-ui.button>
                        @else
                            <form action="{{ route('cart.add-product', $product) }}" method="POST">
                                @csrf
                                <x-ui.button variant="primary" size="lg" type="submit">
                                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                    В корзину
                                </x-ui.button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

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
