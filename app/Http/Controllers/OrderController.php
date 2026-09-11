<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(): Response
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('items')
            ->latest('placed_at')
            ->paginate(10);

        return Inertia::render('Account/Orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'payment', 'address', 'coupon']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'canCancel' => $order->user_id === Auth::id() && in_array($order->status->value, ['PENDING', 'PAID'], true),
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        try {
            $this->checkout->cancelOrder($order);
        } catch (CheckoutException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return back()->with('success', 'Pedido cancelado.');
    }
}
