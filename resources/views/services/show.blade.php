@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Услуги', 'url' => route('services.index')],
                ['label' => $service->title, 'url' => null],
            ]" />

            @php
                $images = $service->images;
                $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <div>
                    @if($cover)
                        <div class="aspect-video lg:aspect-[4/3] rounded-xl overflow-hidden bg-stone-50">
                            <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}">
                                <img src="{{ $cover->url }}" alt="{{ $service->title }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $service->id }}/800/600';">
                            </a>
                        </div>
                    @endif

                    @if($thumbs->count() > 0)
                        <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                            @foreach($thumbs as $image)
                                <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}" class="block w-20 h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                                    <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900 mt-2 mb-3">{{ $service->title }}</h1>
                    @php
                        $reviews = $reviews ?? $service->reviews;
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

            @if(($similarServices ?? collect())->isNotEmpty())
                <section class="mt-10 pt-8 border-t border-stone-200 overflow-hidden">
                    <x-ui.section-heading icon="heroicon-o-wrench-screwdriver" class="mb-4">Похожие услуги</x-ui.section-heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 md:gap-5">
                        @foreach($similarServices as $similar)
                            @include('components.service-card', ['service' => $similar])
                        @endforeach
                    </div>
                </section>
            @endif

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
