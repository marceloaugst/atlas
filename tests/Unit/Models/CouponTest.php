<?php

namespace Tests\Unit\Models;

use App\Enums\CouponType;
use App\Models\Coupon;
use Tests\TestCase;

class CouponTest extends TestCase
{
    public function test_inactive_coupon_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['active' => false]);

        $this->assertFalse($coupon->isValidFor(10000));
    }

    public function test_coupon_before_its_start_date_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['starts_at' => now()->addDay()]);

        $this->assertFalse($coupon->isValidFor(10000));
    }

    public function test_coupon_after_its_expiration_date_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['expires_at' => now()->subDay()]);

        $this->assertFalse($coupon->isValidFor(10000));
    }

    public function test_coupon_at_or_over_its_usage_limit_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 3, 'usage_count' => 3]);

        $this->assertFalse($coupon->isValidFor(10000));
    }

    public function test_coupon_below_its_usage_limit_is_valid(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 3, 'usage_count' => 2]);

        $this->assertTrue($coupon->isValidFor(10000));
    }

    public function test_coupon_below_minimum_amount_is_invalid(): void
    {
        $coupon = $this->makeCoupon(['minimum_amount' => 10000]);

        $this->assertFalse($coupon->isValidFor(9999));
    }

    public function test_coupon_at_minimum_amount_is_valid(): void
    {
        $coupon = $this->makeCoupon(['minimum_amount' => 10000]);

        $this->assertTrue($coupon->isValidFor(10000));
    }

    public function test_percentage_discount_is_calculated_from_subtotal(): void
    {
        $coupon = $this->makeCoupon(['type' => CouponType::Percentage, 'value' => 10]);

        // 10% of R$130,93 (13093 cents), rounded.
        $this->assertSame(1309, $coupon->calculateDiscount(13093));
    }

    public function test_fixed_discount_ignores_subtotal_unless_it_exceeds_it(): void
    {
        $coupon = $this->makeCoupon(['type' => CouponType::Fixed, 'value' => 1500]);

        $this->assertSame(1500, $coupon->calculateDiscount(13093));
    }

    public function test_discount_is_capped_at_maximum_discount(): void
    {
        $coupon = $this->makeCoupon([
            'type' => CouponType::Percentage,
            'value' => 50,
            'maximum_discount' => 2000,
        ]);

        // 50% of 13093 would be 6547, but the cap kicks in.
        $this->assertSame(2000, $coupon->calculateDiscount(13093));
    }

    public function test_discount_never_exceeds_the_subtotal(): void
    {
        $coupon = $this->makeCoupon(['type' => CouponType::Fixed, 'value' => 999999]);

        $this->assertSame(500, $coupon->calculateDiscount(500));
    }

    private function makeCoupon(array $overrides = []): Coupon
    {
        return new Coupon(array_merge([
            'code' => 'TEST10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'minimum_amount' => null,
            'maximum_discount' => null,
            'starts_at' => null,
            'expires_at' => null,
            'usage_limit' => null,
            'usage_count' => 0,
            'active' => true,
        ], $overrides));
    }
}
