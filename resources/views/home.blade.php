@extends('layouts.app')

@section('content')
    {{-- Hero --}}
    <section class="mt-4 md:mt-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="swiper hero-swiper rounded-lg overflow-hidden">
            <div class="swiper-wrapper">
                @foreach($banners as $banner)
                    <div class="swiper-slide">
                        <a href="{{ $banner['link'] }}" class="group/banner block relative h-60 sm:h-72 md:h-[22rem] lg:h-[26rem] xl:h-[28rem] overflow-hidden">
                            <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 hover:scale-[1.02]" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $banner['id'] }}/1920/600';">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent flex items-end">
                                <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pb-5 sm:pb-6 md:pb-8">
                                    <h2 class="banner-title text-xl sm:text-2xl md:text-3xl font-semibold text-white mb-1">{{ $banner['title'] }}</h2>
                                    <p class="banner-desc text-white/90 text-sm mb-4 max-w-md line-clamp-2 sm:line-clamp-none">{{ $banner['description'] }}</p>
                                    <span class="banner-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-sky-600 text-white font-semibold text-sm transition-colors group-hover/banner:bg-sky-700">
                                        Подробнее
                                        <span class="inline-flex transition-transform duration-200 group-hover/banner:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination hero-pagination !bottom-3 sm:!bottom-4 [&>.swiper-pagination-bullet]:!bg-white/70 [&>.swiper-pagination-bullet-active]:!bg-white [&>.swiper-pagination-bullet]:!w-2 [&>.swiper-pagination-bullet]:!h-2"></div>
            <div class="swiper-button-prev hero-prev !text-white !w-9 !h-9 sm:!w-10 sm:!h-10 !bg-black/40 !rounded-md after:!text-sm !hidden sm:!flex"></div>
            <div class="swiper-button-next hero-next !text-white !w-9 !h-9 sm:!w-10 sm:!h-10 !bg-black/40 !rounded-md after:!text-sm !hidden sm:!flex"></div>
            </div>
        </div>
    </section>

    {{-- Categories (управляются в админ-панели) --}}
    <section class="py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-8">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sky-600/80">@svg('heroicon-o-squares-2x2', 'w-5 h-5')</span>
                        <h2 class="text-xl font-semibold text-stone-800 tracking-tight">Категории</h2>
                    </div>
                    <p class="text-stone-500 text-sm mt-1">Выберите раздел каталога</p>
                </div>
                <a href="/products" class="group text-sky-600 hover:text-sky-700 font-medium text-sm inline-flex items-center gap-1.5 shrink-0 transition-colors">
                    Все категории
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6 md:auto-rows-[minmax(140px,1fr)]">
                @foreach($categories as $index => $category)
                    @php
                        $isFeatured = ($category['is_featured'] ?? false) || $index === 0;
                        $visibilityClass = $index === 4 ? 'hidden md:block' : ($index === 5 ? 'hidden md:block lg:hidden' : '');
                    @endphp
                    <div class="{{ $isFeatured ? 'md:col-span-2 md:row-span-2 lg:col-span-2 lg:row-span-2' : '' }} {{ $visibilityClass }}">
                        @include('components.category-card', [
                            'category' => $category,
                            'featured' => $isFeatured,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- New Products --}}
    <section class="py-12 md:py-16 bg-white border-y border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h2 class="text-xl font-semibold text-stone-800 tracking-tight mb-8 flex items-center gap-2">
                <span class="text-sky-600/80">@svg('heroicon-o-sparkles', 'w-5 h-5')</span>
                Новые поступления
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5 md:gap-6">
                @foreach($newProducts as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <x-ui.button href="/products" variant="outline" size="md" class="group">
                    Все товары
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- Recommended --}}
    <section class="py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h2 class="text-xl font-semibold text-stone-800 tracking-tight mb-8 flex items-center gap-2">
                <span class="text-sky-600/80">@svg('heroicon-o-star', 'w-5 h-5')</span>
                Рекомендуемые
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
                @foreach($recommendedProducts as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <x-ui.button href="/products" variant="outline" size="md" class="group">
                    Смотреть каталог
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="py-12 md:py-16 bg-white border-y border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h2 class="text-xl font-semibold text-stone-800 tracking-tight mb-8 flex items-center gap-2">
                <span class="text-sky-600/80">@svg('heroicon-o-wrench-screwdriver', 'w-5 h-5')</span>
                Наши услуги
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                @foreach($services as $service)
                    @include('components.service-card', ['service' => $service])
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <x-ui.button href="/services" variant="outline" size="md" class="group">
                    Все услуги
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- News --}}
    <section class="py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h2 class="text-xl font-semibold text-stone-800 tracking-tight mb-8 flex items-center gap-2">
                <span class="text-sky-600/80">@svg('heroicon-o-newspaper', 'w-5 h-5')</span>
                Последние новости
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 md:gap-6">
                @foreach($news as $item)
                    @include('components.news-card', ['item' => $item])
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <x-ui.button href="/news" variant="subtle" size="md" class="group">
                    Все новости
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            new Swiper('.hero-swiper', {
                loop: true,
                autoplay: { delay: 5000, disableOnInteraction: false },
                pagination: { el: '.hero-pagination', clickable: true },
                navigation: { nextEl: '.hero-next', prevEl: '.hero-prev' },
            });
        }
    });
</script>
@endpush
