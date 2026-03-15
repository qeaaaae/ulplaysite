@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку заказов">
            @svg('heroicon-o-arrow-left', 'w-5 h-5')
            <span class="hidden sm:inline">К списку заказов</span>
        </a>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-stone-200 bg-gradient-to-r from-stone-50/80 to-white">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-sky-100 text-sky-600">
                        @svg('heroicon-o-shopping-cart', 'w-7 h-7')
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-stone-900">Заказ {{ $order->order_number }}</h1>
                        <p class="text-stone-500 text-sm mt-0.5 flex items-center gap-1.5">
                            @svg('heroicon-o-calendar-days', 'w-4 h-4')
                            {{ $order->created_at->format(config('app.datetime_format')) }}
                        </p>
                    </div>
                </div>
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex flex-wrap items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <label class="text-sm font-medium text-stone-600 flex items-center gap-2">
                        @svg('heroicon-o-flag', 'w-4 h-4 text-stone-400')
                        Статус
                    </label>
                    <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 bg-white border border-stone-300 rounded-lg text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[160px]">
                        <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>Новый</option>
                        <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Оплачен</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>В обработке</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Отправлен</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Выполнен</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Отменён</option>
                    </select>
                </form>
            </div>
            @if($order->user)
                <div class="mt-4 pt-4 border-t border-stone-100 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                    <span class="text-stone-500 flex items-center gap-1.5">
                        @svg('heroicon-o-user', 'w-4 h-4')
                        Клиент:
                    </span>
                    <span class="font-medium text-stone-800">{{ $order->user->name }}</span>
                    <a href="mailto:{{ $order->user->email }}" class="text-sky-600 hover:underline">{{ $order->user->email }}</a>
                    @if($order->user->phone)
                        <span class="text-stone-500">{{ $order->user->phone }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="p-5 sm:p-6 border-b border-stone-200">
            <h2 class="flex items-center gap-2 font-medium text-stone-800 mb-4">
                @svg('heroicon-o-truck', 'w-5 h-5 text-sky-600')
                Контакты и доставка
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                @if(!empty($order->contact_info['name']))<dt class="text-stone-500">Имя</dt><dd class="font-medium">{{ $order->contact_info['name'] }}</dd>@endif
                @if(!empty($order->contact_info['email']))<dt class="text-stone-500">Email</dt><dd><a href="mailto:{{ $order->contact_info['email'] }}" class="text-sky-600 hover:underline">{{ $order->contact_info['email'] }}</a></dd>@endif
                @if(!empty($order->contact_info['phone']))<dt class="text-stone-500">Телефон</dt><dd>{{ $order->contact_info['phone'] }}</dd>@endif
                @if(!empty($order->delivery_info['address']))<dt class="text-stone-500">Адрес</dt><dd>{{ $order->delivery_info['address'] }}</dd>@endif
                @if(isset($order->delivery_info['delivery_cost']))<dt class="text-stone-500">Доставка</dt><dd class="tabular-nums">{{ number_format((float) $order->delivery_info['delivery_cost'], 0, ',', ' ') }} ₽</dd>@endif
                @if(!empty($order->payment_info['method']))<dt class="text-stone-500">Оплата</dt><dd>{{ $order->payment_info['method'] === 'card' ? 'Картой' : ($order->payment_info['method'] === 'cash' ? 'Наличными' : $order->payment_info['method']) }}</dd>@endif
            </dl>
            @if($order->comment)
                <div class="mt-4 pt-4 border-t border-stone-100">
                    <dt class="text-stone-500 text-sm mb-1">Комментарий к заказу</dt>
                    <dd class="text-stone-800 whitespace-pre-wrap">{{ $order->comment }}</dd>
                </div>
            @endif
        </div>

        <div class="p-5 sm:p-6">
            <h2 class="flex items-center gap-2 font-medium text-stone-800 mb-4">
                @svg('heroicon-o-list-bullet', 'w-5 h-5 text-sky-600')
                Состав заказа
            </h2>
            <ul class="space-y-3 mb-4">
                @foreach($order->items as $item)
                    <li class="flex justify-between text-sm py-2 border-b border-stone-100 last:border-0">
                        <span class="text-stone-800">{{ $item->title }} × {{ $item->quantity }}</span>
                        <span class="tabular-nums font-medium">{{ number_format($item->subtotal, 0, ',', ' ') }} ₽</span>
                    </li>
                @endforeach
            </ul>
            <div class="border-t-2 border-stone-200 pt-4 flex justify-between items-center text-base font-semibold bg-stone-50 -mx-5 sm:-mx-6 px-5 sm:px-6 py-3 mt-2 rounded-b-xl">
                <span class="text-stone-800">Итого</span>
                <span class="tabular-nums text-sky-700">{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
            </div>
        </div>
    </div>
@endsection
