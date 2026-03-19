@extends('layouts.app')

@section('content')
    <div
        class="py-8 md:py-12"
        x-data="{
            loading: false,
            abortController: null,
            async load(url) {
                if (!url) return;
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                this.loading = true;
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
                        if (el) el.innerHTML = data.html;
                    }
                } catch (e) {
                    // Ignore aborts
                    if (e?.name !== 'AbortError') console.error(e);
                } finally {
                    this.loading = false;
                }
            },
            init() {
                const root = this.$el;

                // Filters (category/sort) are rendered as TomSelect-enhanced selects.
                root.querySelectorAll('select[data-ajax-products-filter]').forEach((sel) => {
                    if (sel.tomselect?.on) {
                        sel.tomselect.on('change', (value) => this.load(value));
                    }
                    sel.addEventListener('change', (e) => this.load(e.target.value));
                });

                // Search submit should update results without full page reload.
                const searchForm = root.querySelector('form[method=\'GET\'][action*=\'/products\']');
                if (searchForm) {
                    searchForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const formData = new FormData(searchForm);
                        const params = new URLSearchParams(formData);
                        const url = searchForm.action + '?' + params.toString();
                        this.load(url);
                    });
                }

                // Pagination links should update results too.
                const resultsEl = document.getElementById('products-results');
                if (resultsEl) {
                    resultsEl.addEventListener('click', (e) => {
                        const a = e.target?.closest?.('a');
                        if (!a) return;
                        if (!a.closest('[data-products-pagination]')) return;
                        const href = a.getAttribute('href');
                        if (!href) return;
                        e.preventDefault();
                        this.load(href);
                    });
                }
            }
        }"
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
                $filterBase = array_filter(['category' => $currentCategory?->slug, 'q' => request('q')]);
                $selectClass = 'w-full sm:min-w-[160px] sm:max-w-full px-3 py-2.5 bg-white border border-stone-300 rounded-lg text-sm font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 appearance-none bg-no-repeat pr-9 bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center]';
            @endphp
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-6 p-4 sm:p-5 mb-6 sm:mb-8 bg-white rounded-xl sm:rounded-2xl border border-stone-200 shadow-sm overflow-visible">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4 md:gap-6">
                    @if($categories->isNotEmpty())
                        <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-3">
                            <span class="text-sm font-medium text-stone-600 sm:shrink-0">Категория</span>
                            <select
                                data-enhance="tom-select"
                                data-ajax-products-filter
                                class="{{ $selectClass }}"
                                style="background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')"
                            >
                                <option value="{{ route('products.index', array_merge($filterBase, ['sort' => $currentSort ?? 'newest'])) }}" {{ !$currentCategory ? 'selected' : '' }}>Все категории</option>
                                @foreach($categories as $cat)
                                    <option value="{{ route('products.index', array_merge($filterBase, ['category' => $cat->slug, 'sort' => $currentSort ?? 'newest'])) }}" {{ ($currentCategory && $currentCategory->id === $cat->id) ? 'selected' : '' }}>
                                        {{ $cat->name }}{{ isset($cat->products_count) ? ' (' . $cat->products_count . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-3">
                        <span class="text-sm font-medium text-stone-600 sm:shrink-0">Сортировка</span>
                        <select
                            data-enhance="tom-select"
                            data-ajax-products-filter
                            class="{{ $selectClass }}"
                            style="background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')"
                        >
                            @php $sortBase = array_merge($filterBase, ['category' => $currentCategory?->slug]); @endphp
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'popular'])) }}" {{ ($currentSort ?? '') === 'popular' ? 'selected' : '' }}>Сначала популярные</option>
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'relevance'])) }}" {{ ($currentSort ?? '') === 'relevance' ? 'selected' : '' }}>По релевантности</option>
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'newest'])) }}" {{ ($currentSort ?? 'newest') === 'newest' ? 'selected' : '' }}>Сначала новые</option>
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'price_asc'])) }}" {{ ($currentSort ?? '') === 'price_asc' ? 'selected' : '' }}>Сначала дешёвые</option>
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'price_desc'])) }}" {{ ($currentSort ?? '') === 'price_desc' ? 'selected' : '' }}>Сначала дорогие</option>
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'rating'])) }}" {{ ($currentSort ?? '') === 'rating' ? 'selected' : '' }}>С высокой оценкой</option>
                        </select>
                    </div>
                </div>
                <x-ui.search-form
                    action="{{ route('products.index') }}"
                    placeholder="Поиск..."
                    :value="request('q')"
                    :hiddens="array_filter(['category' => $currentCategory?->slug, 'sort' => $currentSort ?? 'newest'])"
                    formClass="w-full sm:flex-1 sm:min-w-0"
                />
            </div>

            <div class="relative">
                <div
                    x-show="loading"
                    x-cloak
                    class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center rounded-2xl z-10"
                >
                    <div class="text-stone-600 text-sm">Загрузка...</div>
                </div>

                <div id="products-results">
                    @include('products._results', ['products' => $products, 'cartProductIds' => $cartProductIds ?? []])
                </div>
            </div>
        </div>
    </div>
@endsection
