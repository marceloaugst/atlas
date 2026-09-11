<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->withCount('items')
            ->when($request->string('status')->isNotEmpty(), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'payment', 'address', 'coupon', 'user']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $status = OrderStatus::from($request->string('status')->toString());

        if ($status === OrderStatus::Canceled) {
            try {
                $this->checkout->cancelOrder($order);
            } catch (CheckoutException $e) {
                return back()->withErrors(['status' => $e->getMessage()]);
            }

            return back()->with('success', 'Pedido cancelado.');
        }

        $order->update(['status' => $status]);

        return back()->with('success', 'Status do pedido atualizado.');
    }
}
