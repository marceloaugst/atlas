<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/livros/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho', [CartController::class, 'store'])->name('cart.store');
Route::patch('/carrinho/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
