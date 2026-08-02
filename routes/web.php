<?php

use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ProductVariantController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\Master\AttributeController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CustomerLevelController;
use App\Http\Controllers\Master\LocationDistanceController;
use App\Http\Controllers\Master\LocationNodeController;
use App\Http\Controllers\Master\StoreLevelController;
use App\Http\Controllers\Store\OrdersController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\SubscriptionController;
use App\Http\Controllers\Store\SubscriptionInvoiceController;
use App\Http\Controllers\Store\WalletController;
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

    Route::resource('address', AddressController::class)->except(['show']);
    Route::post('address/{address}/default', [AddressController::class, 'setDefault'])->name('address.default');

    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('checkout/success/{invoice}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('orders', [OrderController::class, 'index'])->name('order.index');
    Route::get('orders/{invoice}', [OrderController::class, 'show'])->name('order.show');

    Route::get('payment/{invoice}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('payment/{invoice}', [PaymentController::class, 'store'])->name('payment.store');
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

    Route::get('orders', [OrdersController::class, 'index'])->name('order.index');
    Route::get('orders/{order}', [OrdersController::class, 'show'])->name('order.show');
    Route::post('orders/{order}/status', [OrdersController::class, 'update'])->name('order.update');

    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet', [WalletController::class, 'store'])->name('wallet.store');
    Route::post('wallet/withdrawal/{withdrawal}/{action}', [WalletController::class, 'process'])->name('wallet.process');
});

Route::middleware(['web', 'auth', 'active'])->prefix('katalog')->name('katalog.')->group(function () {
    Route::resource('product', ProductController::class)->except(['show']);
    Route::resource('product-variant', ProductVariantController::class)->except(['show']);
});
