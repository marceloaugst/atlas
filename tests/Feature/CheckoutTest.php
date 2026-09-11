<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function validAddress(): array
    {
        return [
            'name' => 'Maria Silva',
            'zip_code' => '01310-100',
            'street' => 'Av. Paulista',
            'number' => '1000',
            'complement' => null,
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Maria Silva',
            'customer_email' => 'maria@example.com',
            'address' => $this->validAddress(),
            'payment_method' => 'PIX',
        ], $overrides);
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/carrinho');
    }

    public function test_guest_can_complete_checkout_and_stock_is_decremented(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 5000]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post('/checkout', $this->checkoutPayload());

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(10000, $order->subtotal);
        $this->assertSame(1500, $order->shipping); // below the free-shipping threshold
        $this->assertSame(11500, $order->total);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_clears_the_cart_after_placing_the_order(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/checkout', $this->checkoutPayload());

        $cartResponse = $this->get('/carrinho');
        $cartResponse->assertInertia(fn ($page) => $page->where('cartDetail.items', []));
    }

    public function test_checkout_applies_a_valid_coupon(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 10000]);
        Coupon::create([
            'code' => 'SAVE10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'active' => true,
        ]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/checkout', $this->checkoutPayload(['coupon_code' => 'SAVE10']));

        $order = Order::firstOrFail();
        $this->assertSame(1000, $order->discount);
        $this->assertSame(1, Coupon::where('code', 'SAVE10')->value('usage_count'));
    }

    public function test_checkout_rejects_an_invalid_coupon_without_creating_an_order(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post('/checkout', $this->checkoutPayload(['coupon_code' => 'DOESNOTEXIST']));

        $response->assertSessionHasErrors('checkout');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_checkout_fails_gracefully_when_stock_runs_out_before_confirmation(): void
    {
        $product = Product::factory()->create(['stock' => 2]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 2]);

        // Someone else bought the last units between add-to-cart and checkout.
        $product->update(['stock' => 0]);

        $response = $this->post('/checkout', $this->checkoutPayload());

        $response->assertSessionHasErrors('checkout');
        $this->assertSame(0, Order::count());
    }

    public function test_free_shipping_above_the_threshold(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 15000]);
        $this->post('/carrinho', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/checkout', $this->checkoutPayload());

        $order = Order::firstOrFail();
        $this->assertSame(0, $order->shipping);
        $this->assertSame(15000, $order->total);
    }
}
