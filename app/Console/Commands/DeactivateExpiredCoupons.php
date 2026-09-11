<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('coupons:deactivate-expired')]
#[Description('Deactivate coupons whose expiration date has passed')]
class DeactivateExpiredCoupons extends Command
{
    public function handle(): int
    {
        $count = Coupon::where('active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['active' => false]);

        $this->info("Deactivated {$count} expired coupon(s).");

        return self::SUCCESS;
    }
}
