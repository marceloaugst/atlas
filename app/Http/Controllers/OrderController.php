<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load(['items', 'payment', 'address', 'coupon']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }
}
