@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-stone-100">
                    <h1 class="section-heading text-xl text-stone-900">Заказ {{ $order->order_number }}</h1>
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
                <div class="p-5 sm:p-6">
                    <h2 class="font-semibold text-stone-900 mb-4">Состав заказа</h2>
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

            <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 sm:gap-4">
                <x-ui.button href="{{ route('home') }}" variant="outline" size="lg" class="w-full sm:w-auto justify-center">
                    @svg('heroicon-o-home', 'w-4 h-4')
                    На главную
                </x-ui.button>
                @auth
                    <x-ui.button href="{{ route('orders.index') }}" variant="primary" size="lg" class="w-full sm:w-auto justify-center">
                        @svg('heroicon-o-clipboard-document-list', 'w-4 h-4')
                        Мои заказы
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </div>
@endsection
