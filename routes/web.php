<?php

use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ProductVariantController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\Master\AttributeController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CustomerLevelController;
use App\Http\Controllers\Master\LocationDistanceController;
use App\Http\Controllers\Master\LocationNodeController;
use App\Http\Controllers\Master\StoreLevelController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\SubscriptionController;
use App\Http\Controllers\Store\SubscriptionInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web'])->group(function () {
    Route::get('storefront', [StorefrontController::class, 'index'])->name('storefront.index');
    Route::get('storefront/store/{store}', [StorefrontController::class, 'show'])->name('storefront.store');
    Route::get('storefront/store/{store}/product/{product}', [StorefrontController::class, 'product'])->name('storefront.product');

    Route::get('daftar', [AuthController::class, 'showRegister'])->name('customer.auth.register');
    Route::post('daftar', [AuthController::class, 'register'])->name('customer.auth.register.store');
    Route::get('masuk', [AuthController::class, 'showLogin'])->name('customer.auth.login');
    Route::post('masuk', [AuthController::class, 'login'])->name('customer.auth.login.store');
});

Route::middleware(['web', 'auth:customer', 'active:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
});

Route::middleware(['web', 'auth', 'active'])->prefix('master')->name('master.')->group(function () {
    Route::resource('store-level', StoreLevelController::class)->except(['show']);
    Route::resource('customer-level', CustomerLevelController::class)->except(['show']);
    Route::resource('category', CategoryController::class)->except(['show']);
    Route::resource('attribute', AttributeController::class)->except(['show']);
    Route::resource('location-node', LocationNodeController::class)->except(['show']);
    Route::resource('location-distance', LocationDistanceController::class)->except(['show']);
});

Route::middleware(['web', 'auth', 'active'])->prefix('toko')->name('toko.')->group(function () {
    Route::resource('store', StoreController::class)->except(['show']);
    Route::resource('subscription', SubscriptionController::class)->except(['show']);
    Route::resource('subscription-invoice', SubscriptionInvoiceController::class)->except(['show']);
});

Route::middleware(['web', 'auth', 'active'])->prefix('katalog')->name('katalog.')->group(function () {
    Route::resource('product', ProductController::class)->except(['show']);
    Route::resource('product-variant', ProductVariantController::class)->except(['show']);
});
