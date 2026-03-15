@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <nav class="text-sm text-stone-500 mb-6">
                <a href="{{ route('home') }}" class="hover:text-sky-600">Главная</a>
                <span class="mx-2">/</span>
                <a href="{{ route('services.index') }}" class="hover:text-sky-600">Услуги</a>
                <span class="mx-2">/</span>
                <span class="text-stone-800">{{ $service->title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <div class="aspect-video lg:aspect-[4/3] rounded-xl overflow-hidden bg-stone-50">
                    <img src="{{ $service->image ?: $service->image_path }}" alt="{{ $service->title }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $service->id }}/800/600';">
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900 mb-4">{{ $service->title }}</h1>
                    @if($service->description)
                        <div class="text-stone-600 leading-relaxed prose prose-stone max-w-none">
                            <p>{{ $service->description }}</p>
                        </div>
                    @endif
                    @if($service->price)
                        <p class="mt-6 text-xl font-bold text-stone-900">от {{ number_format($service->price, 0, ',', ' ') }} ₽</p>
                    @endif
                    <div class="flex flex-wrap gap-3 mt-6">
                        <form action="{{ route('cart.add-service', $service) }}" method="POST">
                            @csrf
                            <x-ui.button type="submit" variant="primary" size="md">
                                @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                                В корзину
                            </x-ui.button>
                        </form>
                        <x-ui.button href="{{ route('services.index') }}" variant="outline" size="md">
                            Все услуги
                        </x-ui.button>
                    </div>
                </div>
            </div>

            <x-reviews-block
                :reviewable="$service"
                :reviews="$reviews ?? $service->reviews"
                :can-review="$canReview ?? false"
                store-route="reviews.store.service"
                :store-route-param="$service"
            />
        </div>
    </div>
@endsection
