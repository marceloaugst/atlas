<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'PERCENTAGE';
    case Fixed = 'FIXED';
}
