@props([
    'reviewable', // Product or Service
    'reviews' => collect(),
    'canReview' => false,
    'storeRoute',
    'storeRouteParam',
])

@php
    $reviewableName = $reviewable instanceof \App\Models\Product ? 'товара' : 'услуги';
    $isPaginated = $reviews instanceof \Illuminate\Pagination\AbstractPaginator;
    $reviewTotal = $isPaginated ? $reviews->total() : $reviews->count();
@endphp
<div
    id="reviews"
    class="mt-8 pt-6 border-t border-stone-200 scroll-mt-24"
    x-data="{
        loading: false,
        loadingMore: false,
        abortController: null,
        infiniteObserver: null,
        async load(url, append = false) {
            if (!url) return;
            if (this.abortController) this.abortController.abort();
            this.abortController = new AbortController();
            this.loading = !append;
            this.loadingMore = append;
            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: this.abortController.signal,
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data?.result && data?.html) {
                    const el = document.getElementById('reviews-results');
                    if (!el) return;

                    if (!append) {
                        el.innerHTML = data.html;
                        if (window.Alpine?.initTree) window.Alpine.initTree(el);
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

                        if (window.Alpine?.initTree) window.Alpine.initTree(el);
                    }

                    queueMicrotask(() => this.setupInfiniteScroll());
                }
            } catch (e) {
                if (e?.name !== 'AbortError') console.error(e);
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        setupInfiniteScroll() {
            if (this.infiniteObserver) {
                this.infiniteObserver.disconnect();
                this.infiniteObserver = null;
            }
            const resultsEl = document.getElementById('reviews-results');
            if (!resultsEl) return;
            const sentinel = resultsEl.querySelector('#reviews-infinite-sentinel');
            if (!sentinel) return;
            const nextUrl = sentinel.dataset.nextUrl || '';
            if (!nextUrl) return;
            this.infiniteObserver = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (!entry.isIntersecting) continue;
                        if (this.loading || this.loadingMore) continue;
                        const u = entry.target?.dataset?.nextUrl || '';
                        if (!u) continue;
                        this.infiniteObserver?.disconnect();
                        this.infiniteObserver = null;
                        this.load(u, true);
                        break;
                    }
                },
                { root: null, rootMargin: '480px 0px 0px 0px', threshold: 0 }
            );
            this.infiniteObserver.observe(sentinel);
        },
        init() {
            this.setupInfiniteScroll();
        }
    }"
    x-init="init()"
>
    <h2 class="text-lg font-semibold text-stone-900 mb-5 flex items-center gap-2">
        @svg('heroicon-o-star', 'w-5 h-5 text-sky-500')
        <span>Отзывы {{ $reviewTotal ? '(' . $reviewTotal . ')' : '' }}</span>
    </h2>

    @if($canReview)
    <form action="{{ route($storeRoute, $storeRouteParam) }}" method="POST" enctype="multipart/form-data" data-ajax-review-store class="mb-6 p-5 sm:p-6 bg-stone-50/60 rounded-xl border border-stone-200 shadow-sm space-y-5">
        @csrf
        <div class="space-y-4">
            <div class="form-field" x-data="{ rating: {{ old('rating', 0) }}, hover: 0 }">
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-2">
                    @svg('heroicon-o-star', 'w-4 h-4 text-sky-500')
                    Оценка
                </label>
                <div class="flex gap-1">
                    <input type="hidden" name="rating" :value="rating" required>
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="p-1 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500/30" @click="rating = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0" aria-label="Оценка {{ $i }}">
                            <span class="block text-2xl leading-none" :class="(hover || rating) >= {{ $i }} ? 'text-sky-500' : 'text-stone-300'">★</span>
                        </button>
                    @endfor
                </div>
                <div class="text-xs text-rose-600 hidden mt-1" data-ajax-review-error="rating"></div>
            </div>
            <div class="form-field">
                <label for="review-body" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4 text-sky-500')
                    Текст отзыва
                </label>
                <textarea name="body" id="review-body" rows="3" maxlength="500" class="w-full px-3 py-2.5 bg-white border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors resize-y" placeholder="Поделитесь впечатлениями о товаре...">{{ old('body') }}</textarea>
                <div class="mt-1 text-xs text-rose-600 hidden" data-ajax-review-error="body"></div>
                @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <x-ui.file-input
                    name="images[]"
                    accept="image/*"
                    multiple
                    :max-previews="3"
                    label="Фото (макс. 3)"
                    label-icon="heroicon-o-photo"
                    :error="$errors->first('images')"
                />
                <div class="mt-1.5 text-xs text-rose-600 hidden" data-ajax-review-error="images"></div>
            </div>
        </div>
        <div class="pt-1">
            <x-ui.button type="submit" variant="primary" size="lg">
                @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                Отправить отзыв
            </x-ui.button>
        </div>
    </form>
    @else
        <x-ui.info-banner>
            @auth
                Отзыв — только на купленный {{ $reviewableName }}.
            @else
                <a href="{{ route('login') }}" class="text-sky-600 hover:underline">Войдите</a> и оформите покупку.
            @endauth
        </x-ui.info-banner>
    @endif

    <div class="relative">
        <div id="reviews-results">
            <x-reviews-list :reviews="$reviews" />
        </div>
        <div
            x-show="loading || loadingMore"
            x-cloak
            x-transition.opacity.duration.200ms
            class="flex justify-center py-6 mt-1"
            role="status"
            aria-live="polite"
            aria-busy="true"
            aria-label="Загрузка"
        >
            <span
                class="inline-block h-10 w-10 shrink-0 rounded-full border-4 border-stone-200 border-t-sky-600 animate-spin [animation-duration:0.85s]"
                aria-hidden="true"
            ></span>
        </div>
    </div>
</div>
