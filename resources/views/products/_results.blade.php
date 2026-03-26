@props([
    'products',
    'cartProductIds' => [],
])

@if($products->isEmpty())
    <p class="text-stone-500 py-12 text-center">Товары не найдены</p>
@else
    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
        @foreach($products as $product)
            @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
        @endforeach
    </div>
    @if($products->hasMorePages())
        <div
            id="products-infinite-sentinel"
            data-next-url="{{ $products->nextPageUrl() }}"
            class="h-1 w-full shrink-0 pointer-events-none"
            aria-hidden="true"
        ></div>
    @endif
@endif
