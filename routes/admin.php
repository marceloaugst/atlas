<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoogleBooksController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except('show')->parameters(['products' => 'product:id']);
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('authors', AuthorController::class)->except('show');
    Route::resource('coupons', CouponController::class)->except('show');

    Route::get('pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('pedidos/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('clientes', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('importar-livros', [GoogleBooksController::class, 'index'])->name('google-books.index');
    Route::post('importar-livros', [GoogleBooksController::class, 'store'])->name('google-books.store');
});
