<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/livros/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho', [CartController::class, 'store'])->name('cart.store');
Route::patch('/carrinho/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::middleware('auth')->group(function () {
    Route::get('/minha-conta/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/minha-conta/pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/auth.php';
