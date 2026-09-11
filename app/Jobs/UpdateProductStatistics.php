<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class UpdateProductStatistics implements ShouldQueue
{
    use Queueable;

    public const CACHE_KEY = 'dashboard:metrics';

    public const CACHE_TTL = 7200;

    public function handle(): void
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
            ->get()
            ->toArray();

        Cache::put(self::CACHE_KEY, [
            'metrics' => [
                'salesToday' => (int) $salesToday,
                'salesMonth' => (int) $salesMonth,
                'ordersCount' => $ordersCount,
                'customersCount' => $customersCount,
            ],
            'topProducts' => $topProducts,
            'updatedAt' => now()->toIso8601String(),
        ], self::CACHE_TTL);
    }
}
