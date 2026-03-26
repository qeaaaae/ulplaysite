@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <div class="mb-6 sm:mb-8">
                <x-ui.section-heading tag="h1" icon="heroicon-o-magnifying-glass" class="mb-0">
                    Поиск по сайту
                </x-ui.section-heading>
                <p class="mt-2 text-sm text-stone-500">
                    @if($q !== '')
                        Результаты по запросу: <span class="font-medium text-stone-700">{{ $q }}</span>
                    @else
                        Введите запрос, чтобы найти товары, услуги и новости.
                    @endif
                </p>
            </div>

            <x-ui.search-form
                action="{{ route('search.index') }}"
                placeholder="Поиск по товарам, услугам и новостям..."
                :value="$q"
                formClass="h-11 mb-6 sm:mb-8 max-w-3xl"
            />

            @if($q !== '')
                @php
                    $hasAny = $products->isNotEmpty() || $services->isNotEmpty() || $news->isNotEmpty();
                @endphp

                @if($hasAny)
                    @if($products->isNotEmpty())
                        <section class="mb-8 sm:mb-10">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <x-ui.section-heading tag="h2" icon="heroicon-o-shopping-bag" class="mb-0">Товары</x-ui.section-heading>
                                <a href="{{ route('products.index', ['q' => $q, 'sort' => 'relevance']) }}" class="text-sm font-medium text-sky-600 hover:text-sky-700 transition-colors">Все товары</a>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($products as $product)
                                    @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($services->isNotEmpty())
                        <section class="mb-8 sm:mb-10">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <x-ui.section-heading tag="h2" icon="heroicon-o-wrench-screwdriver" class="mb-0">Услуги</x-ui.section-heading>
                                <a href="{{ route('services.index', ['q' => $q]) }}" class="text-sm font-medium text-sky-600 hover:text-sky-700 transition-colors">Все услуги</a>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($services as $service)
                                    @include('components.service-card', ['service' => $service])
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($news->isNotEmpty())
                        <section>
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <x-ui.section-heading tag="h2" icon="heroicon-o-newspaper" class="mb-0">Новости</x-ui.section-heading>
                                <a href="{{ route('news.index', ['q' => $q]) }}" class="text-sm font-medium text-sky-600 hover:text-sky-700 transition-colors">Все новости</a>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($news as $item)
                                    @include('components.news-card', ['item' => $item])
                                @endforeach
                            </div>
                        </section>
                    @endif
                @else
                    <div class="p-6 sm:p-8 bg-white border border-stone-200 rounded-xl text-center text-stone-500">
                        По вашему запросу ничего не найдено.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

