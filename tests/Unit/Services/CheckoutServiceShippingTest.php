<?php

namespace Tests\Unit\Services;

use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Tests\TestCase;

class CheckoutServiceShippingTest extends TestCase
{
    private CheckoutService $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkout = new CheckoutService(new CartService);
    }

    public function test_shipping_is_free_at_the_threshold(): void
    {
        $this->assertSame(0, $this->checkout->calculateShipping(15000));
    }

    public function test_shipping_is_free_above_the_threshold(): void
    {
        $this->assertSame(0, $this->checkout->calculateShipping(20000));
    }

    public function test_shipping_is_flat_rate_below_the_threshold(): void
    {
        $this->assertSame(1500, $this->checkout->calculateShipping(14999));
    }

    public function test_shipping_is_flat_rate_for_a_zero_subtotal(): void
    {
        $this->assertSame(1500, $this->checkout->calculateShipping(0));
    }
}
