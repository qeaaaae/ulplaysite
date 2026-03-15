@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-shopping-cart', 'w-8 h-8 text-sky-600')
            Заказы
        </h1>
    </div>
    <div class="mb-4 flex flex-wrap gap-3 items-center">
        <x-admin.search-bar :action="route('admin.orders.index')" placeholder="Номер заказа, имя или email..." :value="request('q', '')" />
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-2 items-center">
            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                <option value="">Все статусы</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Оплачен</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>В обработке</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Отправлен</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Выполнен</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Отменён</option>
            </select>
        </form>
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Номер</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Клиент</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Сумма</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                            <td class="px-4 py-3">
                                @if($order->user)
                                    <span class="block">{{ $order->user->name }}</span>
                                    <span class="text-sm text-stone-500">{{ $order->user->email }}</span>
                                @else
                                    <span class="text-stone-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $order->created_at->format(config('app.datetime_format')) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusLabel = match($order->status) {
                                        'new' => 'Новый',
                                        'paid' => 'Оплачен',
                                        'processing' => 'В обработке',
                                        'shipped' => 'Отправлен',
                                        'completed' => 'Выполнен',
                                        'cancelled' => 'Отменён',
                                        default => $order->status
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded
                                    @if($order->status === 'completed') bg-emerald-100 text-emerald-800
                                    @elseif($order->status === 'cancelled') bg-rose-100 text-rose-800
                                    @elseif(in_array($order->status, ['paid', 'processing', 'shipped'])) bg-sky-100 text-sky-800
                                    @else bg-stone-100 text-stone-700
                                    @endif">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium tabular-nums">{{ number_format($order->total, 0, ',', ' ') }} ₽</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex p-2 text-stone-500 hover:text-sky-600 hover:bg-sky-50 rounded-md transition-colors" title="Подробнее">@svg('heroicon-o-eye', 'w-5 h-5')</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-stone-500">Заказов нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $orders->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @forelse($orders as $order)
            <div class="flex justify-between items-start gap-2 bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div>
                    <p class="font-medium">{{ $order->order_number }}</p>
                    <p class="text-sm text-stone-500">{{ $order->user?->name ?? '-' }} · {{ $order->created_at->format(config('app.datetime_format')) }}</p>
                    @php
                        $statusLabel = match($order->status) {
                            'new' => 'Новый',
                            'paid' => 'Оплачен',
                            'processing' => 'В обработке',
                            'shipped' => 'Отправлен',
                            'completed' => 'Выполнен',
                            'cancelled' => 'Отменён',
                            default => $order->status
                        };
                    @endphp
                    <span class="inline-flex mt-1 px-2 py-0.5 text-xs font-medium rounded
                        @if($order->status === 'completed') bg-emerald-100 text-emerald-800
                        @elseif($order->status === 'cancelled') bg-rose-100 text-rose-800
                        @elseif(in_array($order->status, ['paid', 'processing', 'shipped'])) bg-sky-100 text-sky-800
                        @else bg-stone-100 text-stone-700
                        @endif">{{ $statusLabel }}</span>
                </div>
                <div class="text-right shrink-0 flex flex-col items-end gap-1">
                    <p class="font-semibold tabular-nums">{{ number_format($order->total, 0, ',', ' ') }} ₽</p>
                    <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-stone-500 hover:text-sky-600 hover:bg-sky-50 rounded-md transition-colors" title="Подробнее">@svg('heroicon-o-eye', 'w-5 h-5')</a>
                </div>
            </div>
        @empty
            <p class="text-center text-stone-500 py-8">Заказов нет</p>
        @endforelse
        <div class="py-2">{{ $orders->links() }}</div>
    </div>
@endsection
