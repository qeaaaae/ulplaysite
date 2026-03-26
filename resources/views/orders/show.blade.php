@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
                <div class="p-6 border-b border-stone-200">
                    <h1 class="text-xl font-semibold text-stone-900">Заказ {{ $order->order_number }}</h1>
                    <p class="text-stone-500 text-sm mt-1">{{ $order->created_at->format(config('app.datetime_format')) }} · Статус: {{ match($order->status) {
                        'new' => 'Новый',
                        'paid' => 'Оплачен',
                        'processing' => 'В обработке',
                        'shipped' => 'Отправлен',
                        'completed' => 'Выполнен',
                        'cancelled' => 'Отменён',
                        default => $order->status
                    } }}</p>
                    @if($order->comment)
                        <div class="mt-3 p-3 bg-stone-50 rounded-lg">
                            <p class="text-xs text-stone-500 mb-1">Комментарий к заказу</p>
                            <p class="text-stone-700 text-sm whitespace-pre-wrap">{{ $order->comment }}</p>
                        </div>
                    @endif
                </div>
                <div class="p-6">
                    <h2 class="font-medium text-stone-800 mb-3">Состав заказа</h2>
                    <ul class="space-y-3 mb-6">
                        @foreach($order->items as $item)
                            <li class="flex justify-between">
                                <span>{{ $item->title }} × {{ $item->quantity }}</span>
                                <span>{{ number_format($item->subtotal, 0, ',', ' ') }} ₽</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-stone-200 pt-3 flex justify-between font-semibold">
                        <span>Итого</span>
                        <span>{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <x-ui.button href="{{ route('home') }}" variant="outline">На главную</x-ui.button>
                @auth
                    <x-ui.button href="{{ route('orders.index') }}" variant="ghost">Мои заказы</x-ui.button>
                @endauth
            </div>
        </div>
    </div>
@endsection
