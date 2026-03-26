@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-semibold text-stone-900 mb-8">Мои заказы</h1>

            @if($purchasedWithoutReview->isNotEmpty())
                <section class="mb-10 p-6 bg-sky-50 rounded-xl border border-sky-100">
                    <h2 class="text-lg font-semibold text-stone-900 mb-4">Оставить отзыв</h2>
                    <p class="text-sm text-stone-600 mb-4">Вы можете оставить отзыв на купленные товары:</p>
                    <ul class="space-y-2">
                        @foreach($purchasedWithoutReview as $item)
                            <li>
                                <a href="{{ route('products.show', $item['model']) }}#reviews" class="text-sky-600 hover:underline font-medium">{{ $item['model']->title }}</a>
                                <span class="text-stone-500 text-sm"> — товар</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($orders->isEmpty())
                <p class="text-stone-500 py-12">У вас пока нет заказов</p>
            @else
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="block p-6 bg-white rounded-xl border border-stone-200 hover:border-sky-200 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-medium text-stone-900">{{ $order->order_number }}</span>
                                    <p class="text-sm text-stone-500 mt-1">{{ $order->created_at->format(config('app.datetime_format')) }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold text-stone-900">{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
                                    <p class="text-sm text-stone-500">{{ match($order->status) {
                                        'new' => 'Новый',
                                        'paid' => 'Оплачен',
                                        'processing' => 'В обработке',
                                        'shipped' => 'Отправлен',
                                        'completed' => 'Выполнен',
                                        'cancelled' => 'Отменён',
                                        default => $order->status
                                    } }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-8">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
@endsection
