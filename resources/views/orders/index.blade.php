@extends('layouts.app')

@section('content')
    <div
        class="py-4"
        x-data="ordersReviewModal()"
        @orders-review-submitted.window="handleReviewSubmitted($event.detail.productId)"
    >
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <x-ui.section-heading tag="h1" icon="heroicon-o-shopping-bag" class="mb-0">Мои заказы</x-ui.section-heading>

            @if($purchasedWithoutReview->isNotEmpty())
                <section id="leave-review" class="rounded-2xl border border-sky-100 bg-sky-50/90 p-5 sm:p-6 shadow-sm scroll-mt-24">
                    <h2 class="section-heading text-lg flex items-center gap-2.5 text-stone-900 mb-3">
                        <span class="text-sky-600 shrink-0">@svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')</span>
                        Оставить отзыв
                    </h2>
                    <p class="text-sm text-stone-600 mb-4">Можно оценить купленные товары:</p>
                    <ul class="space-y-2">
                        @foreach($purchasedWithoutReview as $item)
                            <li data-product-id="{{ $item['model']->id }}">
                                <button
                                    type="button"
                                    class="text-sky-700 font-medium hover:text-sky-800 underline-offset-2 hover:underline text-left"
                                    @click="openReviewModal({{ json_encode(route('reviews.store.product', $item['model'])) }}, {{ json_encode($item['model']->title) }}, {{ $item['model']->id }})"
                                >
                                    {{ $item['model']->title }}
                                </button>
                                <span class="text-stone-500 text-sm"> — товар</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($orders->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-200 bg-stone-50/50 px-6 py-12 text-center">
                    <p class="text-stone-600">У вас пока нет заказов</p>
                    <x-ui.button href="{{ route('products.index') }}" variant="primary" class="mt-5">
                        @svg('heroicon-o-shopping-bag', 'w-4 h-4')
                        В каталог
                    </x-ui.button>
                </div>
            @else
                <div class="flex flex-col gap-4">
                    @foreach($orders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 sm:p-6 shadow-sm hover:border-sky-300 hover:shadow-md transition-all">
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0">
                                    <span class="font-semibold text-stone-900">{{ $order->order_number }}</span>
                                    <p class="text-sm text-stone-500 mt-1">{{ $order->created_at->format(config('app.datetime_format')) }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold text-stone-900 tabular-nums block">{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
                                    @php
                                        $st = $order->status;
                                        $statusLabel = match ($st) {
                                            'new' => 'Новый',
                                            'paid' => 'Оплачен',
                                            'processing' => 'В обработке',
                                            'shipped' => 'Отправлен',
                                            'completed' => 'Выполнен',
                                            'cancelled' => 'Отменён',
                                            default => $st,
                                        };
                                        $statusBadgeClass = match (true) {
                                            $st === 'new' => 'bg-sky-100 text-sky-800',
                                            in_array($st, ['paid', 'completed'], true) => 'bg-emerald-100 text-emerald-800',
                                            in_array($st, ['processing', 'shipped'], true) => 'bg-amber-100 text-amber-900',
                                            $st === 'cancelled' => 'bg-stone-100 text-stone-700',
                                            default => 'bg-stone-100 text-stone-600',
                                        };
                                    @endphp
                                    <span class="inline-block mt-1.5 text-xs font-medium px-2 py-0.5 rounded-md {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($orders->hasPages())
                    <div class="pt-2 border-t border-stone-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>

        <template x-if="reviewModalOpen">
            <div
                class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @keydown.escape.window="closeReviewModal()"
                role="dialog"
                aria-modal="true"
                aria-labelledby="orders-review-modal-title"
            >
                <div class="absolute inset-0 z-0" @click.self="closeReviewModal()" aria-hidden="true"></div>
                <div
                    class="relative z-10 w-full max-w-lg max-h-[min(90vh,640px)] overflow-y-auto bg-white rounded-2xl border border-stone-200 shadow-xl p-5 sm:p-6"
                    @click.stop
                >
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <h2 id="orders-review-modal-title" class="text-lg font-semibold text-stone-900 pr-2" x-text="productTitle"></h2>
                        <button
                            type="button"
                            class="shrink-0 p-1.5 rounded-lg text-stone-500 hover:bg-stone-100 hover:text-stone-800 transition-colors"
                            @click="closeReviewModal()"
                            aria-label="Закрыть"
                        >
                            @svg('heroicon-o-x-mark', 'w-5 h-5')
                        </button>
                    </div>

                    <template x-for="key in [modalKey]" :key="key">
                        <div>
                            <form
                                method="POST"
                                enctype="multipart/form-data"
                                data-ajax-review-store
                                data-ajax-review-context="orders-modal"
                                x-bind:action="reviewFormAction"
                                x-bind:data-product-id="reviewProductId"
                                class="space-y-5"
                            >
                                @csrf
                                <div class="space-y-4">
                                    <div class="form-field" x-data="{ rating: 0, hover: 0 }">
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
                                        <label for="orders-review-body" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                                            @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4 text-sky-500')
                                            Текст отзыва
                                        </label>
                                        <textarea name="body" id="orders-review-body" rows="3" maxlength="500" class="w-full px-3 py-2.5 bg-white border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors resize-y" placeholder="Поделитесь впечатлениями о товаре..."></textarea>
                                        <div class="mt-1 text-xs text-rose-600 hidden" data-ajax-review-error="body"></div>
                                    </div>
                                    <div class="form-field">
                                        <x-ui.file-input
                                            name="images[]"
                                            accept="image/*"
                                            multiple
                                            :max-previews="3"
                                            label="Фото (макс. 3)"
                                            label-icon="heroicon-o-photo"
                                            id="orders-review-images"
                                            :pointer-open="true"
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
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
@endsection
