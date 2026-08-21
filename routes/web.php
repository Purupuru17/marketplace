<?php

use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ProductVariantController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\ChatController as CustomerChatController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\PointController;
use App\Http\Controllers\Customer\RatingController;
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\Master\AttributeController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CustomerLevelController;
use App\Http\Controllers\Master\LocationDistanceController;
use App\Http\Controllers\Master\LocationNodeController;
use App\Http\Controllers\Master\StoreLevelController;
use App\Http\Controllers\Store\ChatController as StoreChatController;
use App\Http\Controllers\Store\OrdersController;
use App\Http\Controllers\Store\PromotionController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\SubscriptionController;
use App\Http\Controllers\Store\SubscriptionInvoiceController;
use App\Http\Controllers\Store\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('storefront.index');
});

Route::middleware(['web'])->group(function () {
    Route::post('broadcasting/auth', [BroadcastAuthController::class, 'authenticate']);

    Route::get('storefront', [StorefrontController::class, 'index'])->name('storefront.index');
    Route::get('storefront/search', [StorefrontController::class, 'search'])->name('storefront.search');
    Route::get('storefront/store/{store}', [StorefrontController::class, 'show'])->name('storefront.store');
    Route::get('storefront/store/{store}/product/{product}', [StorefrontController::class, 'product'])->name('storefront.product');

    Route::get('daftar', [AuthController::class, 'showRegister'])->name('customer.auth.register');
    Route::post('daftar', [AuthController::class, 'register'])->middleware('throttle:register')->name('customer.auth.register.store');
    Route::get('masuk', [AuthController::class, 'showLogin'])->name('customer.auth.login');
    Route::post('masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('customer.auth.login.store');
});

Route::middleware(['web', 'auth:customer', 'active:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('account', [AccountController::class, 'index'])->name('account');
    Route::get('account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('account', [AccountController::class, 'update'])->name('account.update');

    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart', [CartController::class, 'store'])->middleware('throttle:action')->name('cart.store');
    Route::put('cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::resource('address', AddressController::class)->except(['show']);
    Route::post('address/{address}/default', [AddressController::class, 'setDefault'])->name('address.default');

    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:action')->name('checkout.store');
    Route::get('checkout/success/{invoice}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('orders', [OrderController::class, 'index'])->name('order.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('order.show');
    Route::get('orders/{order}/review', [OrderController::class, 'review'])->name('order.review');
    Route::post('orders/{order}/review', [OrderController::class, 'submitReview'])->middleware('throttle:action')->name('order.review.store');

    Route::get('payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('payment/{payment}/proof', [PaymentController::class, 'upload'])->middleware('throttle:action')->name('payment.proof');

    Route::delete('ratings/{rating}', [RatingController::class, 'destroy'])->name('rating.destroy');

    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorite.index');
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->middleware('throttle:action')->name('favorite.toggle');

    Route::get('points', [PointController::class, 'index'])->name('point.index');

    Route::get('chat', [CustomerChatController::class, 'index'])->name('chat.index');
    Route::post('chat/start', [CustomerChatController::class, 'start'])->middleware('throttle:action')->name('chat.start');
    Route::get('chat/{conversation}', [CustomerChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation}', [CustomerChatController::class, 'store'])->middleware('throttle:action')->name('chat.store');
});

Route::middleware(['web', 'auth', 'active'])->prefix('master')->name('master.')->group(function () {
    Route::get('store-level/ajax', [StoreLevelController::class, 'ajax'])->name('store-level.ajax');
    Route::resource('store-level', StoreLevelController::class)->except(['show']);
    Route::get('customer-level/ajax', [CustomerLevelController::class, 'ajax'])->name('customer-level.ajax');
    Route::resource('customer-level', CustomerLevelController::class)->except(['show']);
    Route::get('category/ajax', [CategoryController::class, 'ajax'])->name('category.ajax');
    Route::resource('category', CategoryController::class)->except(['show']);
    Route::get('attribute/ajax', [AttributeController::class, 'ajax'])->name('attribute.ajax');
    Route::resource('attribute', AttributeController::class)->except(['show']);
    Route::get('location-node/ajax', [LocationNodeController::class, 'ajax'])->name('location-node.ajax');
    Route::resource('location-node', LocationNodeController::class)->except(['show']);
    Route::get('location-distance/ajax', [LocationDistanceController::class, 'ajax'])->name('location-distance.ajax');
    Route::resource('location-distance', LocationDistanceController::class)->except(['show']);
});

Route::middleware(['web', 'auth', 'active'])->prefix('toko')->name('toko.')->group(function () {
    Route::get('store/ajax', [StoreController::class, 'ajax'])->name('store.ajax');
    Route::resource('store', StoreController::class)->except(['show']);
    Route::get('subscription/ajax', [SubscriptionController::class, 'ajax'])->name('subscription.ajax');
    Route::resource('subscription', SubscriptionController::class)->except(['show']);
    Route::get('subscription-invoice/ajax', [SubscriptionInvoiceController::class, 'ajax'])->name('subscription-invoice.ajax');
    Route::resource('subscription-invoice', SubscriptionInvoiceController::class)->except(['show']);

    Route::get('orders/ajax', [OrdersController::class, 'ajax'])->name('order.ajax');
    Route::get('orders', [OrdersController::class, 'index'])->name('order.index');
    Route::get('orders/{order}', [OrdersController::class, 'show'])->name('order.show');
    Route::post('orders/{order}/status', [OrdersController::class, 'update'])->name('order.update');
    Route::post('orders/{order}/paid', [OrdersController::class, 'markPaid'])->name('order.paid');

    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet', [WalletController::class, 'store'])->name('wallet.store');
    Route::post('wallet/withdrawal/{withdrawal}/{action}', [WalletController::class, 'process'])->name('wallet.process');

    Route::get('promotion/ajax', [PromotionController::class, 'ajax'])->name('promotion.ajax');
    Route::resource('promotion', PromotionController::class)->except(['show']);

    Route::get('chat', [StoreChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{conversation}', [StoreChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation}', [StoreChatController::class, 'store'])->name('chat.store');
});

Route::middleware(['web', 'auth', 'active'])->prefix('katalog')->name('katalog.')->group(function () {
    Route::get('product/ajax', [ProductController::class, 'ajax'])->name('product.ajax');
    Route::resource('product', ProductController::class)->except(['show']);
    Route::get('product-variant/ajax', [ProductVariantController::class, 'ajax'])->name('product-variant.ajax');
    Route::resource('product-variant', ProductVariantController::class)->except(['show']);
});

Route::middleware(['web'])->group(base_path('routes/channels.php'));
