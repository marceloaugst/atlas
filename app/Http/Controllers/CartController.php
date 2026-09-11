<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(): Response
    {
        return Inertia::render('Cart/Index', [
            'cartDetail' => $this->carts->current(),
        ]);
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));

        try {
            $this->carts->addItem($product, $request->integer('quantity', 1));
        } catch (CartException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back()->with('success', 'Produto adicionado ao carrinho.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        try {
            $this->carts->updateQuantity($cartItem, $request->integer('quantity'));
        } catch (CartException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back();
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $this->carts->removeItem($cartItem);

        return back();
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        abort_unless($cartItem->cart_id === $this->carts->current()->id, 403);
    }
}
