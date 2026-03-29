@extends('layouts.app')

@php
    $queryParams = array_filter([
        'q' => request('q'),
        'sort' => $currentSort ?? 'newest',
        'category' => $currentCategory?->slug,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@section('content')
    <div
        class="py-4"
        x-data="productsCatalog({
            productsIndexUrl: {{ \Illuminate\Support\Js::from(route('products.index')) }},
            activeCategorySlug: {{ \Illuminate\Support\Js::from($currentCategory?->slug ?? '') }},
            openParents: {{ \Illuminate\Support\Js::from(array_fill_keys($expandParentIds ?? [], true)) }},
        })"
        x-init="init()"
    >
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <x-ui.section-heading tag="h1" icon="heroicon-o-squares-2x2" class="mb-0">
                        @if($currentCategory)
                            {{ $currentCategory->name }}
                        @else
                            Каталог товаров
                        @endif
                    </x-ui.section-heading>
                    <p class="text-stone-500 text-sm mt-1">
                        {{ $products->total() }} товаров
                    </p>
                </div>
            </div>

            @php
                $selectClass = 'h-11 w-full sm:min-w-[240px] sm:max-w-full px-3 py-2.5 bg-white border border-stone-300 rounded-lg text-sm font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 appearance-none bg-no-repeat pr-9 bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center]';
            @endphp

            <div class="flex flex-col gap-8 lg:flex-row lg:items-stretch">
                @include('partials.category-filter-sidebar', [
                    'categoryTree' => $categoryTree,
                    'routeName' => 'products.index',
                    'queryParams' => $queryParams,
                    'expandParentIds' => $expandParentIds ?? [],
                    'type' => 'products',
                ])

                <div class="min-w-0 flex-1 flex flex-col gap-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-6 p-4 sm:p-5 bg-white rounded-xl sm:rounded-2xl border border-stone-200 shadow-sm overflow-visible">
                        <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-3 sm:min-w-0">
                            <span class="text-sm font-medium text-stone-600 sm:shrink-0">Сортировка</span>
                            <select
                                data-enhance="tom-select"
                                data-products-sort
                                class="{{ $selectClass }}"
                                style="background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')"
                            >
                                <option value="popular" {{ ($currentSort ?? '') === 'popular' ? 'selected' : '' }}>Сначала популярные</option>
                                <option value="relevance" {{ ($currentSort ?? '') === 'relevance' ? 'selected' : '' }}>По релевантности</option>
                                <option value="newest" {{ ($currentSort ?? 'newest') === 'newest' ? 'selected' : '' }}>Сначала новые</option>
                                <option value="price_asc" {{ ($currentSort ?? '') === 'price_asc' ? 'selected' : '' }}>Сначала дешёвые</option>
                                <option value="price_desc" {{ ($currentSort ?? '') === 'price_desc' ? 'selected' : '' }}>Сначала дорогие</option>
                                <option value="rating" {{ ($currentSort ?? '') === 'rating' ? 'selected' : '' }}>С высокой оценкой</option>
                            </select>
                        </div>
                        <x-ui.search-form
                            action="{{ route('products.index') }}"
                            placeholder="Поиск..."
                            :value="request('q')"
                            :hiddens="array_filter(['category' => $currentCategory?->slug, 'sort' => $currentSort ?? 'newest'])"
                            formClass="h-11 w-full sm:flex-1 sm:min-w-0"
                        />
                    </div>

                    <div class="relative">
                        <div id="products-results">
                            @include('products._results', ['products' => $products, 'cartProductIds' => $cartProductIds ?? []])
                        </div>
                        <div
                            x-show="loadingMore"
                            x-cloak
                            x-transition.opacity.duration.200ms
                            class="flex justify-center py-8 mt-1"
                            role="status"
                            aria-live="polite"
                            aria-busy="true"
                            aria-label="Загрузка"
                        >
                            <span
                                class="inline-block h-12 w-12 shrink-0 rounded-full border-4 border-stone-200 border-t-sky-600 animate-spin [animation-duration:0.85s]"
                                aria-hidden="true"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function productsCatalog(config) {
        return {
            loading: false,
            loadingMore: false,
            abortController: null,
            infiniteObserver: null,
            productsIndexUrl: config.productsIndexUrl,
            activeCategorySlug: config.activeCategorySlug || '',
            openParents: config.openParents || {},
            toggleParent(id) {
                this.openParents = { ...this.openParents, [id]: !this.openParents[id] };
            },
            buildProductsListUrl(sortKey, categorySlugOverride = null) {
                const u = new URL(this.productsIndexUrl, window.location.origin);
                const params = new URLSearchParams();
                const categorySlug = categorySlugOverride !== null ? categorySlugOverride : this.activeCategorySlug;
                if (categorySlug) params.set('category', categorySlug);
                const qInput = this.$el.querySelector("form[method='GET'][action*='/products'] input[name='q']");
                if (qInput && qInput.value.trim()) params.set('q', qInput.value.trim());
                params.set('sort', sortKey || 'newest');
                const qs = params.toString();
                return u.pathname + (qs ? '?' + qs : '');
            },
            async load(url, append = false) {
                if (!url) return;
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                this.loading = true;
                this.loadingMore = append;
                try {
                    const res = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        signal: this.abortController.signal,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data?.result && data?.html) {
                        const el = document.getElementById('products-results');
                        if (!el) return;
                        if (!append) {
                            el.innerHTML = data.html;
                        } else {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = data.html;
                            const currentGrid = el.querySelector('#products-grid');
                            const newGrid = tmp.querySelector('#products-grid');
                            if (currentGrid && newGrid) {
                                currentGrid.append(...Array.from(newGrid.children));
                            }
                            const currentSentinel = el.querySelector('#products-infinite-sentinel');
                            const newSentinel = tmp.querySelector('#products-infinite-sentinel');
                            if (newSentinel) {
                                if (currentSentinel) currentSentinel.replaceWith(newSentinel);
                                else el.appendChild(newSentinel);
                            } else if (currentSentinel) {
                                currentSentinel.remove();
                            }
                        }
                        try {
                            const u = new URL(url, window.location.origin);
                            this.activeCategorySlug = u.searchParams.get('category') || '';
                        } catch (e) {
                            // ignore URL parse issues
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
                const resultsEl = document.getElementById('products-results');
                if (!resultsEl) return;
                const sentinel = resultsEl.querySelector('#products-infinite-sentinel');
                if (!sentinel) return;
                const nextUrl = sentinel.dataset.nextUrl || '';
                if (!nextUrl) return;
                this.infiniteObserver = new IntersectionObserver(
                    (entries) => {
                        for (const entry of entries) {
                            if (!entry.isIntersecting) continue;
                            if (this.loading) continue;
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
                const root = this.$el;
                root.addEventListener('change', (e) => {
                    const sel = e.target?.closest?.('select[data-products-sort]');
                    if (!sel) return;
                    const sortKey = sel.value;
                    if (!sortKey) return;
                    this.load(this.buildProductsListUrl(sortKey));
                });
                root.querySelectorAll('[data-ajax-products]').forEach((a) => {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        const sortSelect = root.querySelector('select[data-products-sort]');
                        const sortKey = sortSelect?.value || 'newest';
                        const categorySlug = a.dataset.categorySlug || '';
                        this.load(this.buildProductsListUrl(sortKey, categorySlug));
                    });
                });
                const searchForm = root.querySelector("form[method='GET'][action*='/products']");
                if (searchForm) {
                    searchForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const sortSelect = root.querySelector('select[data-products-sort]');
                        const sortKey = sortSelect?.value || 'newest';
                        this.load(this.buildProductsListUrl(sortKey));
                    });
                }
                this.setupInfiniteScroll();
            },
        };
    }
</script>
@endpush
