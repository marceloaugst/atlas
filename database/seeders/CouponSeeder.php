<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'type' => CouponType::Percentage, 'value' => 10, 'maximum_discount' => 5000],
            ['code' => 'TECH20', 'type' => CouponType::Percentage, 'value' => 20, 'minimum_amount' => 10000],
            ['code' => 'BOOK15', 'type' => CouponType::Fixed, 'value' => 1500, 'minimum_amount' => 5000],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                [...$coupon, 'active' => true, 'usage_count' => 0],
            );
        }
    }
}
