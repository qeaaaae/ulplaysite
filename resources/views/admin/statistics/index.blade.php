@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-chart-bar', 'w-8 h-8 text-sky-600')
            Статистика
        </h1>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-sky-100 text-sky-600">@svg('heroicon-o-shopping-cart', 'w-6 h-6')</div>
                <div>
                    <p class="text-sm text-stone-500">Заказов всего</p>
                    <p class="text-2xl font-bold text-stone-900">{{ number_format($totalOrders, 0, ',', ' ') }}</p>
                    <p class="text-xs text-stone-400 mt-0.5">За месяц: {{ $ordersThisMonth }} (было {{ $ordersLastMonth }})</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-emerald-100 text-emerald-600">@svg('heroicon-o-currency-dollar', 'w-6 h-6')</div>
                <div>
                    <p class="text-sm text-stone-500">Выручка</p>
                    <p class="text-2xl font-bold text-stone-900">{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</p>
                    <p class="text-xs text-stone-400 mt-0.5">За месяц: {{ number_format($revenueThisMonth, 0, ',', ' ') }} ₽</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-amber-100 text-amber-600">@svg('heroicon-o-users', 'w-6 h-6')</div>
                <div>
                    <p class="text-sm text-stone-500">Пользователи</p>
                    <p class="text-2xl font-bold text-stone-900">{{ number_format($usersTotal, 0, ',', ' ') }}</p>
                    <p class="text-xs text-stone-400 mt-0.5">Новых за месяц: {{ $usersNewThisMonth }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-stone-100 text-stone-600">@svg('heroicon-o-cube', 'w-6 h-6')</div>
                <div>
                    <p class="text-sm text-stone-500">Товары / Услуги</p>
                    <p class="text-2xl font-bold text-stone-900">{{ $productsCount }} / {{ $servicesCount }}</p>
                    <p class="text-xs text-stone-400 mt-0.5">Новостей: {{ $newsCount }}, отзывов: {{ $reviewsCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-violet-100 text-violet-600">@svg('heroicon-o-banknotes', 'w-6 h-6')</div>
                <div>
                    <p class="text-sm text-stone-500">Средний чек</p>
                    <p class="text-2xl font-bold text-stone-900">{{ number_format($averageOrderValue, 0, ',', ' ') }} ₽</p>
                    <p class="text-xs text-stone-400 mt-0.5">По оплаченным заказам</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Выручка за 30 дней</h2>
            <div class="h-64">
                <canvas id="chartRevenue"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Заказы за 30 дней</h2>
            <div class="h-64">
                <canvas id="chartOrders"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Заказы по статусу</h2>
            <div class="h-64 flex items-center justify-center">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Способ оплаты</h2>
            <div class="h-64 flex items-center justify-center">
                <canvas id="chartPayment"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Заказы по дням недели (30 дней)</h2>
            <div class="h-64">
                <canvas id="chartDow"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-stone-900 mb-4">Регистрации за 30 дней</h2>
            <div class="h-64">
                <canvas id="chartUsers"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200">
                <h2 class="text-lg font-semibold text-stone-900">Топ товаров по продажам</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-stone-500">Товар</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-stone-500">Кол-во</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-stone-500">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($topProducts as $item)
                            <tr class="hover:bg-stone-50/50">
                                <td class="px-4 py-2 text-sm text-stone-800">{{ $item->product?->title ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-right tabular-nums">{{ $item->total_qty }}</td>
                                <td class="px-4 py-2 text-sm text-right tabular-nums">{{ number_format((float) $item->total_sum, 0, ',', ' ') }} ₽</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-stone-500 text-sm">Нет данных</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200">
                <h2 class="text-lg font-semibold text-stone-900">Топ услуг по продажам</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-stone-500">Услуга</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-stone-500">Кол-во</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-stone-500">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($topServices as $item)
                            <tr class="hover:bg-stone-50/50">
                                <td class="px-4 py-2 text-sm text-stone-800">{{ $item->service?->title ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-right tabular-nums">{{ $item->total_qty }}</td>
                                <td class="px-4 py-2 text-sm text-right tabular-nums">{{ number_format((float) $item->total_sum, 0, ',', ' ') }} ₽</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-stone-500 text-sm">Нет данных</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartRevenueLabels = @json($chartRevenueLabels);
            var chartRevenueData = @json($chartRevenueData);
            var chartOrdersData = @json($chartOrdersData);
            var chartDowLabels = @json($chartDowLabels);
            var chartDowData = @json($chartDowData);
            var chartUsersLabels = @json($chartUsersLabels);
            var chartUsersData = @json($chartUsersData);
            var ordersByStatus = @json($ordersByStatus);
            var statusLabels = @json($statusLabels);
            var paymentMethods = @json($paymentMethods);
            var paymentLabels = @json($paymentLabels);

            var sky = { r: 2, g: 132, b: 199 };
            var colors = [
                'rgb(2, 132, 199)',
                'rgb(14, 165, 233)',
                'rgb(56, 189, 248)',
                'rgb(125, 211, 252)',
                'rgb(186, 230, 253)',
            ];

            if (document.getElementById('chartRevenue')) {
                new Chart(document.getElementById('chartRevenue'), {
                    type: 'line',
                    data: {
                        labels: chartRevenueLabels,
                        datasets: [{
                            label: 'Выручка, ₽',
                            data: chartRevenueData,
                            borderColor: 'rgb(2, 132, 199)',
                            backgroundColor: 'rgba(2, 132, 199, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: function(v) { return v >= 1000 ? (v/1000)+'k' : v; } } }
                        }
                    }
                });
            }

            if (document.getElementById('chartOrders')) {
                new Chart(document.getElementById('chartOrders'), {
                    type: 'bar',
                    data: {
                        labels: chartRevenueLabels,
                        datasets: [{
                            label: 'Заказов',
                            data: chartOrdersData,
                            backgroundColor: 'rgba(2, 132, 199, 0.6)',
                            borderColor: 'rgb(2, 132, 199)',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            }

            if (document.getElementById('chartStatus') && Object.keys(ordersByStatus).length) {
                var statusData = [];
                var statusColors = [];
                var statusLabelsArr = [];
                var i = 0;
                for (var k in ordersByStatus) {
                    statusLabelsArr.push(statusLabels[k] || k);
                    statusData.push(ordersByStatus[k]);
                    statusColors.push(colors[i % colors.length]);
                    i++;
                }
                new Chart(document.getElementById('chartStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: statusLabelsArr,
                        datasets: [{ data: statusData, backgroundColor: statusColors, borderWidth: 0 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }

            if (document.getElementById('chartPayment') && Object.keys(paymentMethods).length) {
                var payData = [];
                var payLabels = [];
                var payColors = [];
                var j = 0;
                for (var pk in paymentMethods) {
                    if (pk === 'unknown') continue;
                    payLabels.push(paymentLabels[pk] || pk);
                    payData.push(paymentMethods[pk]);
                    payColors.push(colors[j % colors.length]);
                    j++;
                }
                if (payData.length) {
                    new Chart(document.getElementById('chartPayment'), {
                        type: 'doughnut',
                        data: {
                            labels: payLabels,
                            datasets: [{ data: payData, backgroundColor: payColors, borderWidth: 0 }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'right' } }
                        }
                    });
                }
            }

            if (document.getElementById('chartDow') && chartDowLabels && chartDowData) {
                new Chart(document.getElementById('chartDow'), {
                    type: 'bar',
                    data: {
                        labels: chartDowLabels,
                        datasets: [{
                            label: 'Заказов',
                            data: chartDowData,
                            backgroundColor: 'rgba(2, 132, 199, 0.6)',
                            borderColor: 'rgb(2, 132, 199)',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            }

            if (document.getElementById('chartUsers') && chartUsersLabels && chartUsersData) {
                new Chart(document.getElementById('chartUsers'), {
                    type: 'line',
                    data: {
                        labels: chartUsersLabels,
                        datasets: [{
                            label: 'Регистраций',
                            data: chartUsersData,
                            borderColor: 'rgb(124, 58, 237)',
                            backgroundColor: 'rgba(124, 58, 237, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
