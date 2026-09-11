<?php

namespace Tests\Unit\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises CartService::mergeGuestCartIntoUser() directly, which is where
 * the actual merge logic lives. A session-id bug was found and fixed here
 * during manual browser testing: Auth::login() regenerates the session id
 * internally (session-fixation protection), so the caller must capture the
 * guest session id BEFORE calling Auth::login()/attempt() and pass it in
 * explicitly - re-reading Session::getId() afterwards would silently look
 * up the wrong (already-rotated) id and drop the guest cart.
 */
class CartServiceMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_guest_cart_is_merged_into_a_new_user_cart(): void
    {
        $product = Product::factory()->create();
        $guestCart = Cart::create(['session_id' => 'guest-session-abc']);
        $guestCart->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => $product->price]);
        $user = User::factory()->create();

        app(CartService::class)->mergeGuestCartIntoUser($user, 'guest-session-abc');

        $userCart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(2, $userCart->items()->sum('quantity'));
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
    }

    public function test_merging_sums_quantities_for_a_product_already_in_the_users_cart(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $userCart = Cart::create(['user_id' => $user->id]);
        $userCart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $guestCart = Cart::create(['session_id' => 'guest-session-abc']);
        $guestCart->items()->create(['product_id' => $product->id, 'quantity' => 3, 'unit_price' => $product->price]);

        app(CartService::class)->mergeGuestCartIntoUser($user, 'guest-session-abc');

        $this->assertSame(4, $userCart->items()->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(1, $userCart->items()->count());
    }

    public function test_merging_with_no_matching_guest_cart_is_a_no_op(): void
    {
        $user = User::factory()->create();

        app(CartService::class)->mergeGuestCartIntoUser($user, 'session-that-does-not-exist');

        $this->assertSame(0, Cart::where('user_id', $user->id)->count());
    }

    public function test_merging_leaves_other_products_in_the_users_cart_untouched(): void
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $user = User::factory()->create();
        $userCart = Cart::create(['user_id' => $user->id]);
        $userCart->items()->create(['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => $productA->price]);

        $guestCart = Cart::create(['session_id' => 'guest-session-abc']);
        $guestCart->items()->create(['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => $productB->price]);

        app(CartService::class)->mergeGuestCartIntoUser($user, 'guest-session-abc');

        $this->assertSame(2, $userCart->items()->count());
        $this->assertSame(1, $userCart->items()->where('product_id', $productA->id)->value('quantity'));
        $this->assertSame(1, $userCart->items()->where('product_id', $productB->id)->value('quantity'));
    }
}
