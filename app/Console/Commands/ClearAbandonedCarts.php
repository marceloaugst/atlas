<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('carts:clear-abandoned {--days=7 : Guest carts untouched for this many days are removed}')]
#[Description('Delete guest carts (and their items) abandoned for longer than the given number of days')]
class ClearAbandonedCarts extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $carts = Cart::whereNull('user_id')
            ->where('updated_at', '<', now()->subDays($days))
            ->get();

        $carts->each(fn (Cart $cart) => $cart->delete());

        $this->info("Removed {$carts->count()} abandoned guest cart(s).");

        return self::SUCCESS;
    }
}
