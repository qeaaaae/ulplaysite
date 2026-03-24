@extends('layouts.app')

@section('content')
    @if($banners->isNotEmpty())
    <section class="pt-4 md:pt-5 mb-3 md:mb-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <div class="swiper hero-swiper rounded-2xl overflow-hidden shadow-[0_4px_14px_0_rgba(0,0,0,0.08)]" data-banner-count="{{ $banners->count() }}">
            <div class="swiper-wrapper">
                @foreach($banners as $banner)
                    <div class="swiper-slide">
                        <a href="{{ $banner->link }}" class="group/banner block relative h-[300px] sm:h-72 md:h-[22rem] lg:h-[26rem] xl:h-[28rem] overflow-hidden">
                            <img src="{{ $banner->image }}" alt="{{ $banner->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 hover:scale-[1.02]" onerror="this.onerror=null;this.style.display='none'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent flex items-end">
                                <div class="w-full max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8 pb-5 sm:pb-6 md:pb-8">
                                    <h2 class="banner-title text-xl sm:text-2xl md:text-3xl font-semibold text-white mb-1">{{ $banner->title }}</h2>
                                    <p class="banner-desc text-white/90 text-sm mb-4 max-w-md line-clamp-2 sm:line-clamp-none">{{ $banner->description }}</p>
                                    <span class="banner-btn hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 text-white font-semibold text-sm shadow-lg shadow-sky-900/20 transition-all duration-200 group-hover/banner:bg-sky-700 group-hover/banner:shadow-sky-900/30">
                                        Подробнее
                                        <span class="inline-flex transition-transform duration-200 group-hover/banner:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="hero-pagination absolute bottom-3 left-4 right-4 sm:bottom-4 sm:left-6 sm:right-6 z-10 flex items-center justify-center gap-2">
                @foreach($banners as $i => $b)
                <button type="button" class="hero-pagination-item flex items-center justify-center h-2 cursor-pointer rounded-full {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" aria-label="Слайд {{ $i + 1 }}">
                    <span class="hero-pagination-dot w-2 h-2 rounded-full bg-white/60 shrink-0 transition-opacity duration-200"></span>
                    <span class="hero-pagination-bar hidden h-2 rounded-full bg-white/30 overflow-hidden shrink-0" style="width: 0.5rem;"><span class="hero-pagination-fill block h-full bg-white rounded-full" style="width: 0%; transition: none;"></span></span>
                </button>
                @endforeach
            </div>
            <div class="swiper-button-prev hero-prev !text-white !w-9 !h-9 sm:!w-10 sm:!h-10 !bg-transparent !left-[10px] after:!text-sm !hidden sm:!flex"></div>
            <div class="swiper-button-next hero-next !text-white !w-9 !h-9 sm:!w-10 sm:!h-10 !bg-transparent !right-[10px] after:!text-sm !hidden sm:!flex"></div>
            </div>
        </div>
    </section>
    @endif

    {{-- News --}}
    @if($news->isNotEmpty())
    <section class="pt-2 md:pt-3 pb-3 md:pb-4 mb-3 md:mb-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading icon="heroicon-o-newspaper">Последние новости</x-ui.section-heading>
            <div class="ulplay-home-grid ulplay-home-grid--news grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-4 md:gap-5">
                @foreach($news as $item)
                    @include('components.news-card', ['item' => $item])
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <x-ui.button href="/news" variant="outline" size="md" class="group">
                    Все новости
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>
    @endif

    {{-- Categories --}}
    @if($categories->isNotEmpty())
    <section class="pt-2 md:pt-3 pb-3 md:pb-4 mb-3 md:mb-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-3 md:mb-4">
                <div>
                    <x-ui.section-heading icon="heroicon-o-squares-2x2" class="mb-0">Категории</x-ui.section-heading>
                    <p class="text-stone-500 text-sm mt-1">Выберите раздел каталога</p>
                </div>
                <a href="/products" class="group text-sky-600 hover:text-sky-700 font-medium text-sm inline-flex items-center gap-1.5 shrink-0 transition-colors link-underline">
                    Все категории
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-[1.5fr_1fr_1fr] gap-3 sm:gap-4 md:gap-5 auto-rows-[minmax(120px,1fr)] lg:auto-rows-[minmax(140px,1fr)]">
                @foreach($categories as $index => $category)
                    @php
                        $isFeatured = (bool) ($category->is_featured ?? $category['is_featured'] ?? false);
                        $visibilityClass = match ($index) {
                            4 => 'hidden md:block',
                            5 => 'hidden md:block lg:hidden',
                            default => '',
                        };
                    @endphp
                    <div class="{{ $isFeatured ? 'lg:col-span-1 lg:row-span-2' : '' }} {{ $visibilityClass }}">
                        @include('components.category-card', [
                            'category' => $category,
                            'featured' => $isFeatured,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- New Products --}}
    @if($newProducts->isNotEmpty())
    <section class="pt-2 md:pt-3 pb-3 md:pb-4 mb-3 md:mb-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading icon="heroicon-o-sparkles">Новые поступления</x-ui.section-heading>
            <div class="ulplay-home-grid ulplay-home-grid--new-products grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-4 md:gap-5">
                @foreach($newProducts as $product)
                    @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <x-ui.button href="/products" variant="outline" size="md" class="group">
                    Все товары
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>
    @endif

    {{-- Recommended --}}
    @if($recommendedProducts->isNotEmpty())
    <section class="pt-3 md:pt-4 pb-4 md:pb-5 mb-3 md:mb-4 bg-white">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading icon="heroicon-o-star">Рекомендуемые</x-ui.section-heading>
            <div class="ulplay-home-grid ulplay-home-grid--recommended grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-4 md:gap-5">
                @foreach($recommendedProducts as $product)
                    @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <x-ui.button href="/products" variant="outline" size="md" class="group">
                    Смотреть каталог
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>
    @endif

    {{-- Services --}}
    @if($services->isNotEmpty())
    <section class="pt-3 md:pt-4 pb-3 md:pb-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading icon="heroicon-o-wrench-screwdriver">Наши услуги</x-ui.section-heading>
            <div class="ulplay-home-grid ulplay-home-grid--services grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
                @foreach($services as $service)
                    @include('components.service-card', ['service' => $service])
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <x-ui.button href="/services" variant="outline" size="md" class="group">
                    Все услуги
                    <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
                </x-ui.button>
            </div>
        </div>
    </section>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            var heroEl = document.querySelector('.hero-swiper');
            if (!heroEl) return;
            var bannerCount = parseInt(heroEl.getAttribute('data-banner-count') || '0', 10);
            var autoplayDelay = 5000;
            var heroSwiper = new Swiper('.hero-swiper', {
                loop: bannerCount > 1,
                autoplay: { delay: autoplayDelay, disableOnInteraction: false },
                pagination: false,
                navigation: { nextEl: '.hero-next', prevEl: '.hero-prev' },
            });
            var items = document.querySelectorAll('.hero-pagination-item');
            var prevActiveIdx = 0;
            var stretchDuration = 0.4;
            var barWidthCollapsed = '0.5rem';
            var barWidthExpanded = '3rem';
            function activateItem(idx) {
                var item = items[idx];
                if (!item) return;
                var bar = item.querySelector('.hero-pagination-bar');
                var fill = bar ? bar.querySelector('.hero-pagination-fill') : null;
                item.classList.add('active');
                if (bar) {
                    bar.style.transition = 'none';
                    bar.style.width = barWidthCollapsed;
                }
                if (fill) {
                    fill.style.transition = 'none';
                    fill.style.width = '0%';
                }
                bar && bar.offsetHeight;
                if (bar) {
                    bar.style.transition = 'width ' + stretchDuration + 's ease-out';
                    bar.style.width = barWidthExpanded;
                }
                if (fill) {
                    fill.offsetHeight;
                    fill.style.transition = 'width ' + (autoplayDelay / 1000) + 's linear';
                    fill.style.width = '100%';
                }
            }
            function runProgress(swiper) {
                var idx = swiper.realIndex;
                if (idx === prevActiveIdx) return;
                var prevItem = items[prevActiveIdx];
                var prevBar = prevItem ? prevItem.querySelector('.hero-pagination-bar') : null;
                var prevFill = prevBar ? prevBar.querySelector('.hero-pagination-fill') : null;
                if (prevItem) {
                    prevItem.classList.remove('active');
                    if (prevBar) { prevBar.style.transition = 'none'; prevBar.style.width = barWidthCollapsed; }
                    if (prevFill) { prevFill.style.transition = 'none'; prevFill.style.width = '0%'; }
                }
                prevActiveIdx = idx;
                activateItem(idx);
            }
            heroSwiper.on('slideChangeTransitionEnd', function() { runProgress(heroSwiper); });
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    var i = parseInt(item.getAttribute('data-index'), 10);
                    heroSwiper.slideTo(i);
                });
            });
            activateItem(0);
        }
    });
</script>
@endpush
