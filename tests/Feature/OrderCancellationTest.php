<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function placeOrderAs(User $user, Product $product, int $quantity = 1, ?string $couponCode = null): Order
    {
        $this->actingAs($user)->post('/carrinho', ['product_id' => $product->id, 'quantity' => $quantity]);

        $this->actingAs($user)->post('/checkout', [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'address' => [
                'name' => $user->name,
                'zip_code' => '01310-100',
                'street' => 'Av. Paulista',
                'number' => '1000',
                'complement' => null,
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            'payment_method' => 'PIX',
            'coupon_code' => $couponCode,
        ]);

        return Order::where('user_id', $user->id)->firstOrFail();
    }

    public function test_owner_can_cancel_a_paid_order_and_stock_is_restored(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->placeOrderAs($user, $product, 2);

        $this->assertSame(3, $product->fresh()->stock);

        $response = $this->actingAs($user)->post("/minha-conta/pedidos/{$order->uuid}/cancelar");

        $response->assertSessionHasNoErrors();
        $this->assertSame(OrderStatus::Canceled, $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cancelling_an_order_decrements_the_coupons_usage_count(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5, 'price' => 10000]);
        Coupon::create([
            'code' => 'SAVE10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'active' => true,
        ]);
        $order = $this->placeOrderAs($user, $product, 1, 'SAVE10');
        $this->assertSame(1, Coupon::where('code', 'SAVE10')->value('usage_count'));

        $this->actingAs($user)->post("/minha-conta/pedidos/{$order->uuid}/cancelar");

        $this->assertSame(0, Coupon::where('code', 'SAVE10')->value('usage_count'));
    }

    public function test_a_user_cannot_cancel_someone_elses_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->placeOrderAs($owner, $product);

        $response = $this->actingAs($intruder)->post("/minha-conta/pedidos/{$order->uuid}/cancelar");

        $response->assertForbidden();
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_a_shipped_order_can_no_longer_be_cancelled(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->placeOrderAs($user, $product);
        $order->update(['status' => OrderStatus::Shipped]);

        $response = $this->actingAs($user)->post("/minha-conta/pedidos/{$order->uuid}/cancelar");

        $response->assertSessionHasErrors('order');
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }
}
