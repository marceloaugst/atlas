<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_active_product_with_stock_is_purchasable(): void
    {
        $product = new Product(['active' => true, 'stock' => 1]);

        $this->assertTrue($product->isPurchasable());
    }

    public function test_inactive_product_is_not_purchasable(): void
    {
        $product = new Product(['active' => false, 'stock' => 10]);

        $this->assertFalse($product->isPurchasable());
    }

    public function test_product_without_stock_is_not_purchasable(): void
    {
        $product = new Product(['active' => true, 'stock' => 0]);

        $this->assertFalse($product->isPurchasable());
    }
}
