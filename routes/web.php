<?php

use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ProductVariantController;
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
Route::middleware(['web', 'auth:customer', 'active:customer'])->group(function () {
    
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
