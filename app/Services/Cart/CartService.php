<?php

namespace App\Services\Cart;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function current(): Cart
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            $cart = Cart::find(Session::get('cart_id'));

            if (! $cart) {
                $cart = Cart::create(['session_id' => Session::getId()]);
            }
        }

        Session::put('cart_id', $cart->id);

        return $cart->load(['items.product.category', 'items.product.authors']);
    }

    /**
     * Lightweight cart summary that never creates a cart as a side effect,
     * so anonymous page views don't leave orphan cart rows behind.
     */
    public function summary(): array
    {
        $cartId = Auth::check()
            ? Cart::where('user_id', Auth::id())->value('id')
            : Session::get('cart_id');

        if (! $cartId) {
            return ['count' => 0, 'subtotal' => 0];
        }

        $items = CartItem::where('cart_id', $cartId)->get(['quantity', 'unit_price']);

        return [
            'count' => (int) $items->sum('quantity'),
            'subtotal' => (int) $items->sum(fn (CartItem $item) => $item->quantity * $item->unit_price),
        ];
    }

    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        $this->assertPurchasable($product, $quantity);

        $cart = $this->current();

        $item = $cart->items()->where('product_id', $product->id)->first();
        $newQuantity = ($item?->quantity ?? 0) + $quantity;

        if ($newQuantity > $product->stock) {
            throw new CartException('Quantidade solicitada excede o estoque disponível.');
        }

        return $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $newQuantity, 'unit_price' => $product->price],
        );
    }

    public function updateQuantity(CartItem $item, int $quantity): ?CartItem
    {
        if ($quantity <= 0) {
            $this->removeItem($item);

            return null;
        }

        if ($quantity > $item->product->stock) {
            throw new CartException('Quantidade solicitada excede o estoque disponível.');
        }

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    private function assertPurchasable(Product $product, int $quantity): void
    {
        if (! $product->isPurchasable()) {
            throw new CartException('Este produto não está disponível para compra.');
        }

        if ($quantity < 1) {
            throw new CartException('Quantidade inválida.');
        }
    }
}
