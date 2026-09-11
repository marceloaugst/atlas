<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_product_to_the_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 1000]);

        $response = $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 2]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1000,
        ]);
    }

    public function test_adding_a_product_twice_increments_the_existing_line_instead_of_duplicating_it(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 2]);

        $this->assertSame(1, CartItem::where('product_id', $product->id)->count());
        $this->assertSame(3, CartItem::where('product_id', $product->id)->value('quantity'));
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 3]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_cannot_add_an_inactive_product(): void
    {
        $product = Product::factory()->create(['active' => false, 'stock' => 5]);

        $response = $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_can_update_the_quantity_of_a_cart_item(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::where('product_id', $product->id)->firstOrFail();

        $response = $this->patch("/carrinho/{$item->id}", ['quantity' => 4]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(4, $item->fresh()->quantity);
    }

    public function test_updating_quantity_beyond_stock_fails(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::where('product_id', $product->id)->firstOrFail();

        $response = $this->patch("/carrinho/{$item->id}", ['quantity' => 4]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame(1, $item->fresh()->quantity);
    }

    public function test_updating_quantity_to_zero_removes_the_item(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::where('product_id', $product->id)->firstOrFail();

        $this->patch("/carrinho/{$item->id}", ['quantity' => 0]);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_can_remove_a_cart_item(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::where('product_id', $product->id)->firstOrFail();

        $this->delete("/carrinho/{$item->id}");

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_cannot_modify_a_cart_item_belonging_to_another_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $someoneElsesCart = Cart::create(['session_id' => 'someone-elses-session']);
        $item = $someoneElsesCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        // Visiting the storefront first establishes this test's own guest cart/session.
        $this->get('/');

        $response = $this->patch("/carrinho/{$item->id}", ['quantity' => 2]);

        $response->assertForbidden();
    }
}
