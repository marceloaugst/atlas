<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Exceptions\CheckoutException;
use App\Http\Requests\PlaceOrderRequest;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->carts->current();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $couponCode = $request->string('coupon')->toString() ?: null;
        $couponError = null;
        $summary = null;

        try {
            $summary = $this->checkout->summary($cart, $couponCode);
        } catch (CheckoutException $e) {
            $couponError = $e->getMessage();
            $summary = $this->checkout->summary($cart);
        }

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'summary' => [
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['discount'],
                'shipping' => $summary['shipping'],
                'total' => $summary['total'],
                'coupon' => $summary['coupon']?->code,
            ],
            'couponCode' => $couponCode,
            'couponError' => $couponError,
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->checkout->placeOrder(
                address: $request->validated('address'),
                customerName: $request->string('customer_name')->toString(),
                customerEmail: $request->string('customer_email')->toString(),
                paymentMethod: $request->enum('payment_method', PaymentMethod::class),
                couponCode: $request->string('coupon_code')->toString() ?: null,
            );
        } catch (CheckoutException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Pedido realizado com sucesso!');
    }
}
