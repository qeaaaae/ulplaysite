@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-shopping-cart', 'w-8 h-8 text-sky-600')
            Заказы
        </h1>
    </div>

    <div class="mb-4 flex flex-col sm:flex-row sm:flex-wrap gap-3 items-stretch sm:items-center">
        <x-admin.search-bar :action="route('admin.orders.index')" placeholder="Номер заказа, имя или email..." :value="request('q', '')" />
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-2 items-center">
            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <select name="status" data-enhance="tom-select" data-submit-on-change class="h-11 px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[140px]">
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

    <div class="space-y-2">
        @forelse($orders as $order)
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
                $paymentIconClass = $paymentMethod === 'card' ? 'bg-sky-100 text-sky-600 group-hover:bg-sky-50' : ($paymentMethod === 'cash' ? 'bg-emerald-100 text-emerald-600 group-hover:bg-emerald-50' : 'bg-stone-100 text-stone-500 group-hover:bg-sky-50 group-hover:text-sky-600');
            @endphp
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 bg-white rounded-xl border border-stone-200 hover:border-sky-200 hover:shadow-sm transition-all group">
                <div class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors {{ $paymentIconClass }}" title="{{ $paymentMethod === 'card' ? 'Картой' : ($paymentMethod === 'cash' ? 'Наличными' : '') }}">
                    @svg($paymentIcon, 'w-5 h-5')
                </div>
                <div class="min-w-0 flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-1">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <span class="font-semibold text-stone-900">{{ $order->order_number }}</span>
                        <span class="inline-flex ml-2 px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="text-sm text-stone-600 truncate">
                        @if($order->user)
                            {{ $order->user->name }}
                            <span class="hidden sm:inline text-stone-400"> · </span>
                            <span class="hidden sm:inline text-stone-500 truncate">{{ $order->user->email }}</span>
                        @else
                            <span class="text-stone-400">Гость</span>
                        @endif
                    </div>
                    <div class="text-sm text-stone-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span>{{ $order->created_at->format('d.m.Y') }}</span>
                        <span class="text-stone-300">·</span>
                        <span>{{ $order->items_count }} поз.</span>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <span class="font-semibold tabular-nums text-stone-900">{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
                    <span class="text-stone-300 group-hover:text-sky-500 transition-colors">@svg('heroicon-o-chevron-right', 'w-5 h-5')</span>
                </div>
            </a>
        @empty
            <div class="p-8 bg-white rounded-xl border border-stone-200 text-center text-stone-500">
                Заказов нет
            </div>
        @endforelse
    </div>

    @if($orders->isNotEmpty())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
