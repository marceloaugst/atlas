<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Declined = 'DECLINED';
    case Refunded = 'REFUNDED';
}
