<?php

use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\ChatController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\FavoriteController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\PaymentController;
use App\Http\Controllers\Api\Customer\PointController;
use App\Http\Controllers\Api\Customer\RatingController;
use App\Http\Controllers\Api\Customer\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('customer/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('customer/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('storefront/stores', [StorefrontController::class, 'stores']);
    Route::get('storefront/stores/{store}', [StorefrontController::class, 'store']);
    Route::get('storefront/stores/{store}/products/{product}', [StorefrontController::class, 'product']);
    Route::get('storefront/products', [StorefrontController::class, 'products']);
    Route::get('storefront/categories', [StorefrontController::class, 'categories']);
    Route::get('storefront/home', [StorefrontController::class, 'home']);
    Route::get('storefront/search', [StorefrontController::class, 'search']);
    Route::get('storefront/location-nodes', [StorefrontController::class, 'locationNodes']);

    Route::middleware(['auth:api-customer'])->group(function () {
        Route::get('customer/me', [AuthController::class, 'me']);
        Route::put('customer/me', [AuthController::class, 'update']);
        Route::post('customer/change-password', [AuthController::class, 'changePassword']);
        Route::post('customer/logout', [AuthController::class, 'logout']);

        Route::get('customer/addresses', [AddressController::class, 'index']);
        Route::post('customer/addresses', [AddressController::class, 'store']);
        Route::put('customer/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('customer/addresses/{address}', [AddressController::class, 'destroy']);
        Route::post('customer/addresses/{address}/default', [AddressController::class, 'setDefault']);

        Route::get('customer/cart', [CartController::class, 'index']);
        Route::post('customer/cart', [CartController::class, 'store'])->middleware('throttle:action');
        Route::put('customer/cart/{item}', [CartController::class, 'update'])->middleware('throttle:action');
        Route::delete('customer/cart/{item}', [CartController::class, 'destroy']);

        Route::get('customer/checkout/summary', [CheckoutController::class, 'summary']);
        Route::post('customer/checkout', [CheckoutController::class, 'store'])->middleware('throttle:action');

        Route::get('customer/orders', [OrderController::class, 'index']);
        Route::get('customer/orders/{order}', [OrderController::class, 'show']);

        Route::get('customer/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('customer/payments/{payment}/proof', [PaymentController::class, 'upload'])->middleware('throttle:action');

        Route::get('customer/points', [PointController::class, 'index']);

        Route::get('customer/ratings/eligible', [RatingController::class, 'eligible']);
        Route::post('customer/ratings', [RatingController::class, 'store'])->middleware('throttle:action');
        Route::delete('customer/ratings/{rating}', [RatingController::class, 'destroy']);

        Route::get('customer/favorites', [FavoriteController::class, 'index']);
        Route::post('customer/favorites/toggle', [FavoriteController::class, 'toggle'])->middleware('throttle:action');

        Route::get('customer/chats', [ChatController::class, 'index']);
        Route::post('customer/chats/start', [ChatController::class, 'start'])->middleware('throttle:action');
        Route::get('customer/chats/{conversation}', [ChatController::class, 'show']);
        Route::post('customer/chats/{conversation}', [ChatController::class, 'store'])->middleware('throttle:action');
    });

});
