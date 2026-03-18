<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\News;
use App\Models\NewsView;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $last30Days = $now->copy()->subDays(30);

        $ordersQuery = Order::whereNotIn('status', ['cancelled']);
        $ordersPaidQuery = Order::whereIn('status', ['paid', 'processing', 'shipped', 'completed']);

        $totalOrders = (clone $ordersQuery)->count();
        $totalRevenue = (float) (clone $ordersPaidQuery)->sum('total');
        $ordersThisMonth = (clone $ordersQuery)->where('created_at', '>=', $startOfMonth)->count();
        $revenueThisMonth = (float) (clone $ordersPaidQuery)->where('created_at', '>=', $startOfMonth)->sum('total');
        $ordersLastMonth = (clone $ordersQuery)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $revenueLastMonth = (float) (clone $ordersPaidQuery)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('total');

        $usersTotal = User::withoutTrashed()->count();
        $usersNewThisMonth = User::withoutTrashed()->where('created_at', '>=', $startOfMonth)->count();
        $productsCount = Product::count();
        $servicesCount = Service::count();
        $newsCount = News::count();
        $reviewsCount = Review::count();
        $commentsCount = Comment::count();

        $ordersByStatus = Order::selectRaw('status, count(*) as cnt')
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        $revenueByDay = Order::whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('date(created_at) as d, sum(total) as s')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $ordersByDay = Order::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('date(created_at) as d, count(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels30 = [];
        $revenueData = [];
        $ordersData = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $labels30[] = $now->copy()->subDays($i)->format('d.m');
            $revenueData[] = (float) ($revenueByDay->get($d)?->s ?? 0);
            $ordersData[] = (int) ($ordersByDay->get($d)?->c ?? 0);
        }

        $paymentMethods = Order::whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy(fn (Order $o) => $o->payment_info['method'] ?? 'unknown')
            ->map(fn ($group) => $group->count())
            ->all();

        $topProducts = OrderItem::whereNotNull('product_id')
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'processing', 'shipped', 'completed']))
            ->select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(quantity * price) as total_sum'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(10)
            ->get();

        $topServices = OrderItem::whereNotNull('service_id')
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'processing', 'shipped', 'completed']))
            ->select('service_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(quantity * price) as total_sum'))
            ->groupBy('service_id')
            ->orderByDesc('total_qty')
            ->with('service')
            ->limit(10)
            ->get();

        $statusLabels = [
            'new' => 'Новые',
            'paid' => 'Оплачены',
            'processing' => 'В обработке',
            'shipped' => 'Отправлены',
            'completed' => 'Выполнены',
        ];
        $paymentLabels = [
            'cash' => 'Наличные',
            'card' => 'Карта',
        ];

        $paidOrdersCount = (clone $ordersPaidQuery)->count();
        $averageOrderValue = $paidOrdersCount > 0 ? round($totalRevenue / $paidOrdersCount, 0) : 0;

        $ordersForDow = Order::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $last30Days)
            ->get(['created_at']);
        $ordersByDayOfWeek = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        foreach ($ordersForDow as $order) {
            $dow = (int) $order->created_at->format('w');
            $ordersByDayOfWeek[$dow]++;
        }
        $dowLabels = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        $chartDowLabels = $dowLabels;
        $chartDowData = array_values($ordersByDayOfWeek);

        $usersByDay = User::withoutTrashed()
            ->where('created_at', '>=', $last30Days)
            ->where('is_admin', false)
            ->selectRaw('date(created_at) as d, count(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');
        $usersData30 = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $usersData30[] = (int) ($usersByDay->get($d)?->c ?? 0);
        }

        // Активность по новостям: просмотры и комментарии
        $newsViewsLast30 = NewsView::where('created_at', '>=', $last30Days)->get(['created_at']);
        $commentsLast30 = Comment::where('created_at', '>=', $last30Days)->get(['created_at']);

        $newsByDayOfWeekViews = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        foreach ($newsViewsLast30 as $view) {
            $dow = (int) $view->created_at->format('w');
            $newsByDayOfWeekViews[$dow]++;
        }

        $newsByDayOfWeekComments = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        foreach ($commentsLast30 as $comment) {
            $dow = (int) $comment->created_at->format('w');
            $newsByDayOfWeekComments[$dow]++;
        }

        $newsViewsByHour = array_fill(0, 24, 0);
        foreach ($newsViewsLast30 as $view) {
            $hour = (int) $view->created_at->format('G');
            $newsViewsByHour[$hour]++;
        }

        $hourLabels = [];
        for ($h = 0; $h < 24; $h++) {
            $hourLabels[] = sprintf('%02d:00', $h);
        }

        return view('admin.statistics.index', [
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'ordersLastMonth' => $ordersLastMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'usersTotal' => $usersTotal,
            'usersNewThisMonth' => $usersNewThisMonth,
            'productsCount' => $productsCount,
            'servicesCount' => $servicesCount,
            'newsCount' => $newsCount,
            'reviewsCount' => $reviewsCount,
            'commentsCount' => $commentsCount,
            'ordersByStatus' => $ordersByStatus,
            'statusLabels' => $statusLabels,
            'paymentMethods' => $paymentMethods,
            'paymentLabels' => $paymentLabels,
            'chartRevenueLabels' => $labels30,
            'chartRevenueData' => $revenueData,
            'chartOrdersLabels' => $labels30,
            'chartOrdersData' => $ordersData,
            'topProducts' => $topProducts,
            'topServices' => $topServices,
            'averageOrderValue' => $averageOrderValue,
            'chartDowLabels' => $chartDowLabels,
            'chartDowData' => $chartDowData,
            'chartUsersLabels' => $labels30,
            'chartUsersData' => $usersData30,
            'newsChartDowLabels' => $dowLabels,
            'newsChartDowViews' => array_values($newsByDayOfWeekViews),
            'newsChartDowComments' => array_values($newsByDayOfWeekComments),
            'newsChartHourLabels' => $hourLabels,
            'newsChartHourViews' => array_values($newsViewsByHour),
        ]);
    }
}
