<div id="cart-root" class="py-6 md:py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
        <h1 class="text-2xl font-semibold text-stone-900 mb-6 md:mb-8">Корзина</h1>

        @if($items->isEmpty())
            <div class="text-center py-12 sm:py-16 bg-white rounded-2xl border border-stone-200 shadow-sm">
                <p class="text-stone-500 mb-6">Корзина пуста</p>
                <x-ui.button href="{{ route('products.index') }}" variant="primary">
                    @svg('heroicon-o-shopping-bag', 'w-4 h-4')
                    Перейти в каталог
                </x-ui.button>
            </div>
        @else
            <div class="space-y-3 sm:space-y-4 mb-6 md:mb-8">
                @foreach($items as $item)
                    @php
                        $maxQty = max(0, (int) $item->product->stock);
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 p-4 sm:p-5 bg-white rounded-2xl border border-stone-200 shadow-sm">
                        <div class="w-full sm:w-24 h-40 sm:h-24 flex-shrink-0 rounded-xl overflow-hidden bg-stone-100">
                            <img src="{{ $item->product->image }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'">
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('products.show', $item->product) }}" class="font-medium text-stone-900 hover:text-sky-600 line-clamp-2">
                                    {{ $item->title }}
                                </a>
                                <p class="text-sm text-stone-500 mt-0.5">
                                    {{ number_format($item->price, 0, ',', ' ') }} ₽
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                <div class="inline-flex items-center rounded-xl border border-stone-300 bg-stone-50 overflow-hidden">
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="inline" data-ajax-cart-update>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ max(0, $item->quantity - 1) }}">
                                        <button type="submit" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors touch-manipulation cursor-pointer" aria-label="Уменьшить">
                                            @svg('heroicon-o-minus', 'w-5 h-5')
                                        </button>
                                    </form>
                                    <span class="min-w-[2.5rem] sm:min-w-[3rem] text-center text-sm font-medium text-stone-900 tabular-nums">{{ $item->quantity }}</span>
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="inline" data-ajax-cart-update>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                        <button type="submit" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-colors touch-manipulation {{ $item->quantity >= $maxQty ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer' }}" aria-label="Увеличить" @if($item->quantity >= $maxQty) disabled @endif>
                                            @svg('heroicon-o-plus', 'w-5 h-5')
                                        </button>
                                    </form>
                                </div>
                                <form method="POST" action="{{ route('cart.remove', $item) }}" class="inline" data-ajax-cart-remove data-confirm-message="Удалить из корзины?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition-colors touch-manipulation cursor-pointer" title="Удалить" aria-label="Удалить">
                                        @svg('heroicon-o-trash', 'w-5 h-5')
                                    </button>
                                </form>
                            </div>
                            <div class="font-semibold text-stone-900 text-lg sm:text-right sm:w-28 tabular-nums">
                                {{ number_format($item->subtotal, 0, ',', ' ') }} ₽
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 sm:p-6 bg-white rounded-2xl border border-stone-200 shadow-sm">
                <p class="text-xl font-semibold text-stone-900">Итого: {{ number_format($total, 0, ',', ' ') }} ₽</p>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <form method="POST" action="{{ route('cart.clear') }}" class="inline" data-ajax-cart-clear data-confirm-message="Очистить корзину? Все товары будут удалены.">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium border border-stone-300 text-stone-600 hover:border-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer">
                            @svg('heroicon-o-trash', 'w-4 h-4')
                            Очистить корзину
                        </button>
                    </form>
                    <x-ui.button href="{{ route('checkout') }}" variant="primary" size="lg" class="w-full sm:w-auto justify-center">
                        @svg('heroicon-o-credit-card', 'w-5 h-5')
                        Оформить заказ
                    </x-ui.button>
                </div>
            </div>
        @endif
    </div>
</div>

