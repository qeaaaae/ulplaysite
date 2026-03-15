@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="section-heading text-2xl">
                        @if($currentCategory)
                            {{ $currentCategory->name }}
                        @else
                            Каталог товаров
                        @endif
                    </h1>
                    <p class="text-stone-500 text-sm mt-1">
                        {{ $products->total() }} товаров
                    </p>
                </div>
            </div>

            @php
                $filterBase = array_filter(['category' => $currentCategory?->slug, 'q' => request('q')]);
                $selectClass = 'w-full sm:min-w-[160px] sm:max-w-full px-3 py-2.5 bg-white border border-stone-300 rounded-lg text-sm font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 appearance-none bg-no-repeat pr-9 bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center]';
                $selectStyle = "background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')";
            @endphp
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-6 p-4 sm:p-5 mb-6 sm:mb-8 bg-white rounded-xl sm:rounded-2xl border border-stone-200 shadow-sm overflow-visible">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4 md:gap-6">
                    @if($categories->isNotEmpty())
                        <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-3">
                            <span class="text-sm font-medium text-stone-600 sm:shrink-0">Категория</span>
                            <select
                                data-enhance="tom-select"
                                data-redirect-on-change
                                class="{{ $selectClass }}"
                                style="{{ $selectStyle }}"
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
                            data-redirect-on-change
                            class="{{ $selectClass }}"
                            style="{{ $selectStyle }}"
                        >
                            @php $sortBase = array_merge($filterBase, ['category' => $currentCategory?->slug]); @endphp
                            <option value="{{ route('products.index', array_merge($sortBase, ['sort' => 'popular'])) }}" {{ ($currentSort ?? '') === 'popular' ? 'selected' : '' }}>Сначала популярные</option>
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

            @if($products->isEmpty())
                <p class="text-stone-500 py-12 text-center">Товары не найдены</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
                    @foreach($products as $product)
                        @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
