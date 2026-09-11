<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_count',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function isValidFor(int $subtotal): bool
    {
        if (! $this->active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $this->starts_at->greaterThan($now)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lessThan($now)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        if ($this->minimum_amount !== null && $subtotal < $this->minimum_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        $discount = $this->type === CouponType::Percentage
            ? (int) round($subtotal * $this->value / 100)
            : $this->value;

        if ($this->maximum_discount !== null) {
            $discount = min($discount, $this->maximum_discount);
        }

        return min($discount, $subtotal);
    }
}
