<?php

namespace App\Services\Cart;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
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

    /**
     * Folds the guest (session) cart into the user's persistent cart on
     * login/registration, so items added before authenticating aren't lost.
     *
     * The caller must capture the session id BEFORE calling Auth::login()/
     * Auth::attempt() — logging in regenerates the session id internally
     * (session-fixation protection), so by the time this runs Session::getId()
     * would no longer match the guest cart's stored session_id.
     */
    public function mergeGuestCartIntoUser(User $user, string $guestSessionId): void
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()->where('product_id', $guestItem->product_id)->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'quantity' => $guestItem->quantity,
                    'unit_price' => $guestItem->unit_price,
                ]);
            }
        }

        $guestCart->delete();
        Session::put('cart_id', $userCart->id);
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
