@props([
    'products',
    'cartProductIds' => [],
])

@if($products->isEmpty())
    <p class="text-stone-500 py-12 text-center">Товары не найдены</p>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
        @foreach($products as $product)
            @include('components.product-card', ['product' => $product, 'cartProductIds' => $cartProductIds ?? []])
        @endforeach
    </div>
    <div class="mt-8" data-products-pagination>
        {{ $products->withQueryString()->links() }}
    </div>
@endif

