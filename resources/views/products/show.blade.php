@extends('layouts.app')

@section('content')
    <div
        x-data="{
            reviewsModalOpen: false,
            loadingReviews: false,
            loadingMoreReviews: false,
            abortController: null,
            infiniteObserver: null,
            reviewsIndexUrl: {{ \Illuminate\Support\Js::from(route('reviews.index.product', $product)) }},
            openReviewsModal() {
                this.reviewsModalOpen = true;
                queueMicrotask(() => this.setupReviewsInfiniteScroll());
            },
            closeReviewsModal() {
                this.reviewsModalOpen = false;
                if (this.infiniteObserver) {
                    this.infiniteObserver.disconnect();
                    this.infiniteObserver = null;
                }
            },
            async loadReviews(url, append = false) {
                if (!url) return;
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                this.loadingReviews = !append;
                this.loadingMoreReviews = append;
                try {
                    const res = await fetch(url, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        signal: this.abortController.signal,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data?.result && data?.html) {
                        const el = document.getElementById('reviews-modal-results');
                        if (!el) return;
                        if (!append) {
                            el.innerHTML = data.html;
                        } else {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = data.html;
                            const currentGrid = el.querySelector('#reviews-grid');
                            const newGrid = tmp.querySelector('#reviews-grid');
                            if (currentGrid && newGrid) {
                                currentGrid.append(...Array.from(newGrid.children));
                            }
                            const currentSentinel = el.querySelector('#reviews-infinite-sentinel');
                            const newSentinel = tmp.querySelector('#reviews-infinite-sentinel');
                            if (newSentinel) {
                                if (currentSentinel) currentSentinel.replaceWith(newSentinel);
                                else el.appendChild(newSentinel);
                            } else if (currentSentinel) {
                                currentSentinel.remove();
                            }
                        }
                        queueMicrotask(() => this.setupReviewsInfiniteScroll());
                    }
                } catch (e) {
                    if (e?.name !== 'AbortError') console.error(e);
                } finally {
                    this.loadingReviews = false;
                    this.loadingMoreReviews = false;
                }
            },
            setupReviewsInfiniteScroll() {
                if (!this.reviewsModalOpen) return;
                if (this.infiniteObserver) {
                    this.infiniteObserver.disconnect();
                    this.infiniteObserver = null;
                }
                const resultsEl = document.getElementById('reviews-modal-results');
                if (!resultsEl) return;
                const sentinel = resultsEl.querySelector('#reviews-infinite-sentinel');
                if (!sentinel) return;
                const nextUrl = sentinel.dataset.nextUrl || '';
                if (!nextUrl) return;
                const modalScroll = document.getElementById('reviews-modal-scroll');
                this.infiniteObserver = new IntersectionObserver(
                    (entries) => {
                        for (const entry of entries) {
                            if (!entry.isIntersecting) continue;
                            if (this.loadingReviews || this.loadingMoreReviews) continue;
                            const u = entry.target?.dataset?.nextUrl || '';
                            if (!u) continue;
                            this.infiniteObserver?.disconnect();
                            this.infiniteObserver = null;
                            this.loadReviews(u, true);
                            break;
                        }
                    },
                    { root: modalScroll, rootMargin: '320px 0px 0px 0px', threshold: 0 }
                );
                this.infiniteObserver.observe(sentinel);
            }
        }"
        @keydown.escape.window="if (reviewsModalOpen) closeReviewsModal()"
    >
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
                $videoEmbedUrl = $product->video_embed_url;
                $videoPreviewUrl = app(\App\Services\VideoEmbedService::class)->toPreviewImageUrl($product->video_url);
                $hasVideo = !empty($videoEmbedUrl);
                $hasCover = $cover !== null;
                $totalMedia = $images->count() + ($hasVideo ? 1 : 0);
                $hasSimilar = ($similarProducts ?? collect())->isNotEmpty();
            @endphp

            <div class="min-w-0">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-6 lg:gap-8">
                        <div
                            x-data="{
                                showingVideo: false,
                                activeImageId: {{ $cover ? $cover->id : 'null' }},
                                activeSrc: {{ \Illuminate\Support\Js::from($cover?->url ?? '') }},
                                videoEmbedUrl: {{ \Illuminate\Support\Js::from($videoEmbedUrl ?? '') }},
                                videoPreviewUrl: {{ \Illuminate\Support\Js::from($videoPreviewUrl ?? '') }},
                                selectPhoto(id, src) {
                                    this.activeImageId = id;
                                    this.activeSrc = src;
                                    this.showingVideo = false;
                                },
                                selectVideo() {
                                    this.showingVideo = true;
                                },
                                openLightbox() {
                                    if (this.showingVideo) return;
                                    const el = document.getElementById('lb-img-' + this.activeImageId);
                                    if (el) el.click();
                                }
                            }"
                        >
                            <div class="sr-only">
                                @foreach($images as $image)
                                    <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="product-{{ $product->id }}" id="lb-img-{{ $image->id }}"></a>
                                @endforeach
                            </div>

                            <div @class([
                                'lg:grid lg:grid-cols-[6.5rem_minmax(0,1fr)] lg:gap-3' => $totalMedia > 1,
                            ])>
                                @if($cover || $hasVideo)
                                    <div
                                        @class([
                                            'aspect-[4/3] md:aspect-square rounded-xl overflow-hidden bg-stone-50 ring-1 ring-stone-200/50',
                                            'lg:col-start-2 lg:row-start-1' => $totalMedia > 1,
                                        ])
                                    >
                                        <div x-show="!showingVideo" class="w-full h-full" @click="openLightbox()">
                                            <template x-if="activeSrc">
                                                <img :src="activeSrc" alt="{{ $product->title }}" class="w-full h-full object-cover cursor-zoom-in">
                                            </template>
                                            <template x-if="!activeSrc && videoPreviewUrl">
                                                <button
                                                    type="button"
                                                    @click.stop="selectVideo()"
                                                    class="relative block w-full h-full cursor-pointer"
                                                    aria-label="Воспроизвести видео"
                                                >
                                                    <img :src="videoPreviewUrl" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                                    <span class="absolute inset-0 bg-black/35"></span>
                                                    <span class="absolute inset-0 flex items-center justify-center">
                                                        <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/90 text-stone-900 shadow-lg">
                                                            <svg class="w-8 h-8 ml-0.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M8 5.14v14l11-7-11-7z"/>
                                                            </svg>
                                                        </span>
                                                    </span>
                                                </button>
                                            </template>
                                            <template x-if="!activeSrc && !videoPreviewUrl">
                                                <button
                                                    type="button"
                                                    @click.stop="selectVideo()"
                                                    class="w-full h-full flex items-center justify-center bg-stone-900 text-white/90 cursor-pointer"
                                                    aria-label="Воспроизвести видео"
                                                >
                                                    <svg class="w-14 h-14" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5.14v14l11-7-11-7z"/>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                        <div x-show="showingVideo" x-cloak class="w-full h-full bg-stone-900">
                                            <template x-if="showingVideo">
                                                <iframe :src="videoEmbedUrl" class="w-full h-full" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
                                            </template>
                                        </div>
                                    </div>
                                @endif

                                @if($totalMedia > 1)
                                    <div class="mt-3 lg:mt-0 grid grid-cols-4 sm:grid-cols-5 md:grid-cols-4 lg:flex lg:flex-col gap-2 lg:overflow-y-auto pr-0 lg:pr-1 lg:col-start-1 lg:row-start-1">
                                        @foreach($images as $image)
                                            <button
                                                type="button"
                                                @click="selectPhoto({{ $image->id }}, {{ \Illuminate\Support\Js::from($image->url) }})"
                                                class="block w-full aspect-square lg:min-h-[84px] lg:flex-1 rounded-lg overflow-hidden border bg-stone-50 transition-all cursor-pointer outline-none"
                                                :class="!showingVideo && activeImageId === {{ $image->id }} ? 'border-sky-500 ring-2 ring-sky-500/30' : 'border-stone-200 hover:border-stone-300'"
                                            >
                                                <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                        @if($hasVideo)
                                            <button
                                                type="button"
                                                @click="selectVideo()"
                                                class="relative w-full aspect-square lg:min-h-[84px] lg:flex-1 rounded-lg overflow-hidden border bg-stone-900 transition-all cursor-pointer outline-none"
                                                :class="showingVideo ? 'border-sky-500 ring-2 ring-sky-500/30' : 'border-stone-700 hover:border-stone-500'"
                                            >
                                                <template x-if="videoPreviewUrl">
                                                    <img :src="videoPreviewUrl" alt="" class="w-full h-full object-cover">
                                                </template>
                                                <span class="absolute inset-0 bg-black/25"></span>
                                                <span class="absolute inset-0 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white/90" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5.14v14l11-7-11-7z"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <h1 class="text-2xl sm:text-3xl font-semibold text-stone-900 mb-2">{{ $product->title }}</h1>
                            @if(!empty($product->resolved_avito_url))
                                <a
                                    href="{{ $product->resolved_avito_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mb-3 inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 hover:border-sky-400 hover:text-sky-700 hover:bg-stone-50 transition-colors"
                                >
                                    <img alt="Авито" loading="lazy" width="24" height="24" decoding="async" style="color:transparent" src="/Avito Logo.svg">
                                    На Авито
                                </a>
                            @endif

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
                                    <button type="button" @click="openReviewsModal()" class="inline-flex items-center gap-1.5 text-sm text-stone-600 hover:text-sky-600 transition-colors cursor-pointer">
                                        {{ $reviewsCount }} @if($reviewsCount === 1)отзыв@elseif($reviewsCount >= 2 && $reviewsCount <= 4)отзыва@else отзывов @endif
                                        @svg('heroicon-o-chevron-down', 'w-4 h-4')
                                    </button>
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
            </div>

            @if($hasSimilar)
                <section class="mt-10 border-t border-stone-200">
                    <x-ui.section-heading afterBorder icon="heroicon-o-squares-2x2" class="mb-4">Похожие товары</x-ui.section-heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @foreach($similarProducts as $similar)
                            <div class="min-w-0">
                                @include('components.product-card', ['product' => $similar, 'cartProductIds' => $cartProductIds ?? []])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-6 border-t border-stone-200">
                <a href="{{ $product->category ? route('products.index', ['category' => $product->category->slug]) : route('products.index') }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                    {{ $product->category ? $product->category->name : 'В каталог' }}
                </a>
            </footer>
        </div>

        <div
            x-show="reviewsModalOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[180] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="product-reviews-modal-title"
        >
            <div class="absolute inset-0" @click="closeReviewsModal()" aria-hidden="true"></div>
            <div class="relative w-full max-w-3xl bg-white rounded-2xl border border-stone-200 shadow-xl">
                <div class="px-5 sm:px-6 py-4 border-b border-stone-200 flex items-center justify-between gap-3">
                    <h2 id="product-reviews-modal-title" class="text-lg font-semibold text-stone-900 inline-flex items-center gap-2">
                        @svg('heroicon-o-star', 'w-5 h-5 text-sky-500')
                        Отзывы о товаре
                    </h2>
                    <button type="button" @click="closeReviewsModal()" class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors cursor-pointer" aria-label="Закрыть">
                        @svg('heroicon-o-x-mark', 'w-5 h-5')
                    </button>
                </div>

                <div id="reviews-modal-scroll" class="max-h-[72vh] overflow-y-auto p-4 sm:p-6 ulplay-scrollbar-sky">
                    <div id="reviews-modal-results">
                        <x-reviews-list :reviews="$reviews" />
                    </div>
                    <div
                        x-show="loadingReviews || loadingMoreReviews"
                        x-cloak
                        x-transition.opacity.duration.200ms
                        class="flex justify-center py-6 mt-1"
                        role="status"
                        aria-live="polite"
                        aria-busy="true"
                        aria-label="Загрузка"
                    >
                        <span class="inline-block h-10 w-10 shrink-0 rounded-full border-4 border-stone-200 border-t-sky-600 animate-spin [animation-duration:0.85s]" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
