<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    private const FREE_SHIPPING_THRESHOLD = 15000;

    private const FLAT_SHIPPING_RATE = 1500;

    public function __construct(private readonly CartService $carts)
    {
    }

    public function calculateShipping(int $subtotal): int
    {
        return $subtotal >= self::FREE_SHIPPING_THRESHOLD ? 0 : self::FLAT_SHIPPING_RATE;
    }

    public function findValidCoupon(string $code, int $subtotal): Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            throw new CheckoutException('Cupom inválido ou expirado.');
        }

        return $coupon;
    }

    public function summary(Cart $cart, ?string $couponCode = null): array
    {
        $subtotal = $cart->subtotal();
        $coupon = $couponCode ? $this->findValidCoupon($couponCode, $subtotal) : null;
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;
        $shipping = $this->calculateShipping($subtotal - $discount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $subtotal - $discount + $shipping,
            'coupon' => $coupon,
        ];
    }

    /**
     * @param  array{name: string, zip_code: string, street: string, number: string, complement: ?string, neighborhood: string, city: string, state: string}  $address
     */
    public function placeOrder(
        array $address,
        string $customerName,
        string $customerEmail,
        PaymentMethod $paymentMethod,
        ?string $couponCode = null,
    ): Order {
        $cart = $this->carts->current();

        if ($cart->items->isEmpty()) {
            throw new CheckoutException('Seu carrinho está vazio.');
        }

        return DB::transaction(function () use ($cart, $address, $customerName, $customerEmail, $paymentMethod, $couponCode) {
            // Row-lock and re-read every product now, inside the transaction, so
            // the stock we validate against is guaranteed current at the moment
            // payment is confirmed — not the possibly stale value the cart/page
            // was holding — and the same locked rows are reused below to decrement,
            // closing the gap between the check and the write.
            $lockedProducts = [];

            foreach ($cart->items as $item) {
                try {
                    $product = $item->product->refreshForUpdate();
                } catch (ModelNotFoundException) {
                    throw new CheckoutException("O produto \"{$item->product->title}\" não está mais disponível.");
                }

                if (! $product->isPurchasable() || $product->stock < $item->quantity) {
                    throw new CheckoutException("O produto \"{$item->product->title}\" não tem mais estoque suficiente.");
                }

                $lockedProducts[$item->id] = $product;
            }

            $summary = $this->summary($cart, $couponCode);

            $addressModel = Address::create([
                ...$address,
                'user_id' => Auth::id(),
            ]);

            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => Auth::id(),
                'address_id' => $addressModel->id,
                'coupon_id' => $summary['coupon']?->id,
                'status' => OrderStatus::Pending,
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['discount'],
                'shipping' => $summary['shipping'],
                'total' => $summary['total'],
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_title' => $item->product->title,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * $item->unit_price,
                ]);

                $lockedProducts[$item->id]->decrement('stock', $item->quantity);
            }

            $summary['coupon']?->increment('usage_count');

            // Payment is simulated per project scope: no real gateway is integrated,
            // so every attempt is approved immediately.
            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => PaymentStatus::Approved,
                'transaction_id' => (string) Str::uuid(),
                'amount' => $summary['total'],
                'paid_at' => now(),
            ]);

            $order->update(['status' => OrderStatus::Paid]);

            $this->carts->clear($cart);

            return $order;
        });
    }

    public function cancelOrder(Order $order): void
    {
        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Paid], true)) {
            throw new CheckoutException('Este pedido não pode mais ser cancelado.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                // A single-row increment() is already an atomic `UPDATE ... SET stock = stock + ?`,
                // so no preceding SELECT ... FOR UPDATE is needed here (unlike placeOrder, which
                // reads the stock value first to decide whether the order can proceed at all).
                $item->product()->increment('stock', $item->quantity);
            }

            $order->coupon?->decrement('usage_count');

            $order->update(['status' => OrderStatus::Canceled]);
        });
    }
}
