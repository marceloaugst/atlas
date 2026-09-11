<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $paidStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Processing->value,
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
        ];

        $salesToday = Order::whereIn('status', $paidStatuses)
            ->whereDate('placed_at', today())
            ->sum('total');

        $salesMonth = Order::whereIn('status', $paidStatuses)
            ->whereYear('placed_at', now()->year)
            ->whereMonth('placed_at', now()->month)
            ->sum('total');

        $ordersCount = Order::whereIn('status', $paidStatuses)->count();
        $customersCount = User::where('is_admin', false)->count();

        $topProducts = OrderItem::query()
            ->select('product_title')
            ->selectRaw('SUM(quantity) as total_sold')
            ->whereHas('order', fn ($query) => $query->whereIn('status', $paidStatuses))
            ->groupBy('product_title')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'salesToday' => (int) $salesToday,
                'salesMonth' => (int) $salesMonth,
                'ordersCount' => $ordersCount,
                'customersCount' => $customersCount,
            ],
            'topProducts' => $topProducts,
        ]);
    }
}
