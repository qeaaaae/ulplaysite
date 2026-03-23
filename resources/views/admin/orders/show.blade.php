@extends('layouts.admin')

@section('content')
    @php
        $statusLabel = match($order->status) {
            'new' => 'Новый',
            'paid' => 'Оплачен',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменён',
            default => $order->status,
        };
        $statusClass = match($order->status) {
            'completed' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-rose-100 text-rose-800',
            default => in_array($order->status, ['paid', 'processing', 'shipped']) ? 'bg-sky-100 text-sky-800' : 'bg-stone-100 text-stone-700',
        };
        $paymentMethod = $order->payment_info['method'] ?? null;
        $paymentIcon = $paymentMethod === 'card' ? 'heroicon-o-credit-card' : ($paymentMethod === 'cash' ? 'heroicon-o-banknotes' : 'heroicon-o-shopping-bag');
        $paymentIconBg = $paymentMethod === 'card' ? 'bg-sky-100 text-sky-600' : ($paymentMethod === 'cash' ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-stone-500');
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
                @svg('heroicon-o-arrow-left', 'w-5 h-5')
            </a>
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl {{ $paymentIconBg }}">
                    @svg($paymentIcon, 'w-8 h-8')
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900">Заказ {{ $order->order_number }}</h1>
                    <p class="text-sm text-stone-500 flex items-center gap-2 flex-wrap">
                        <span>{{ $order->created_at->format(config('app.datetime_format')) }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                    </p>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label class="text-sm font-medium text-stone-600 flex items-center gap-2">
                @svg('heroicon-o-flag', 'w-4 h-4 text-sky-500')
                Статус
            </label>
            <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 h-11 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[160px]">
                <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Оплачен</option>
                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>В обработке</option>
                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Отправлен</option>
                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Выполнен</option>
                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Отменён</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-admin.form-section title="Клиент и доставка" icon="heroicon-o-user">
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                    <div>
                        <dt class="text-stone-500 mb-0.5">Позиций</dt>
                        <dd class="font-medium text-stone-800">{{ $order->items->count() }}</dd>
                    </div>
                    @php($deliveryType = $order->delivery_info['type'] ?? 'delivery')
                    <div>
                        <dt class="text-stone-500 mb-0.5">Получение</dt>
                        <dd class="text-stone-800">{{ $deliveryType === 'pickup' ? 'Самовывоз' : 'Доставка' }}</dd>
                    </div>
                </div>
                <div>
                    <dt class="text-stone-500 mb-0.5">Клиент</dt>
                    <dd class="text-stone-800 space-y-0.5">
                        @if($order->user)
                            <div>{{ $order->user->name }}</div>
                            <div><a href="mailto:{{ $order->user->email }}" class="text-sky-600 hover:underline">{{ $order->user->email }}</a></div>
                            @if($order->user->phone)
                                <div><a href="tel:{{ preg_replace('/\D/', '', $order->user->phone) }}" class="text-sky-600 hover:underline">{{ $order->user->phone }}</a></div>
                            @endif
                        @elseif(!empty($order->contact_info['name']) || !empty($order->contact_info['email']))
                            @if(!empty($order->contact_info['name']))<div>{{ $order->contact_info['name'] }}</div>@endif
                            @if(!empty($order->contact_info['email']))<div><a href="mailto:{{ $order->contact_info['email'] }}" class="text-sky-600 hover:underline">{{ $order->contact_info['email'] }}</a></div>@endif
                            @if(!empty($order->contact_info['phone']))<div><a href="tel:{{ preg_replace('/\D/', '', $order->contact_info['phone']) }}" class="text-sky-600 hover:underline">{{ $order->contact_info['phone'] }}</a></div>@endif
                        @else
                            <span class="text-stone-400">Гость</span>
                        @endif
                    </dd>
                </div>
                @if(!empty($order->contact_info['name']) && $order->user && $order->contact_info['name'] !== $order->user->name)
                    <div>
                        <dt class="text-stone-500 mb-0.5">Получатель</dt>
                        <dd class="text-stone-800">{{ $order->contact_info['name'] }}@if(!empty($order->contact_info['phone'])) · <a href="tel:{{ preg_replace('/\D/', '', $order->contact_info['phone']) }}" class="text-sky-600 hover:underline">{{ $order->contact_info['phone'] }}</a>@endif</dd>
                    </div>
                @endif
                @if(!empty($order->delivery_info['address']))
                    <div>
                        <dt class="text-stone-500 mb-0.5">Адрес</dt>
                        <dd class="text-stone-800">{{ $order->delivery_info['address'] }}</dd>
                    </div>
                @endif
                <div class="flex flex-wrap gap-x-4 gap-y-1 pt-2 border-t border-stone-100">
                    @if(isset($order->delivery_info['delivery_cost']))
                        <span class="text-stone-600">Доставка: <span class="font-medium tabular-nums">{{ number_format((float) $order->delivery_info['delivery_cost'], 0, ',', ' ') }} ₽</span></span>
                    @endif
                    <span class="text-stone-600">Оплата: {{ $paymentMethod === 'card' ? 'картой' : ($paymentMethod === 'cash' ? 'наличными' : '—') }}</span>
                </div>
                @if($order->comment)
                    <div class="pt-2 border-t border-stone-100">
                        <dt class="text-stone-500 mb-1">Комментарий</dt>
                        <dd class="bg-stone-100 rounded-lg px-3 py-2.5 text-stone-800 whitespace-pre-wrap text-sm">{{ $order->comment }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.form-section>

        <x-admin.form-section title="Состав заказа" icon="heroicon-o-list-bullet">
            <ul class="space-y-2 text-sm">
                @foreach($order->items as $item)
                    <li class="flex justify-between gap-3 py-2 border-b border-stone-100 last:border-0">
                        @if($item->product_id && $item->product)
                            <a href="{{ route('products.show', $item->product) }}" class="text-stone-800 min-w-0 truncate hover:text-sky-600 hover:underline" target="_blank">{{ $item->title }} × {{ $item->quantity }}</a>
                        @elseif($item->service_id && $item->service)
                            <a href="{{ route('services.show', $item->service) }}" class="text-stone-800 min-w-0 truncate hover:text-sky-600 hover:underline" target="_blank">{{ $item->title }} × {{ $item->quantity }}</a>
                        @else
                            <span class="text-stone-800 min-w-0 truncate">{{ $item->title }} × {{ $item->quantity }}</span>
                        @endif
                        <span class="tabular-nums font-medium shrink-0">{{ number_format($item->subtotal, 0, ',', ' ') }} ₽</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 pt-4 border-t-2 border-stone-200 flex justify-between items-center font-semibold">
                <span class="text-stone-800">Итого</span>
                <span class="tabular-nums text-stone-900">{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
            </div>
        </x-admin.form-section>
    </div>
@endsection
