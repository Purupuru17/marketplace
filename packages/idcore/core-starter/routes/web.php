<?php

use IdCore\CoreStarter\Http\Controllers\Auth\LoginController;
use IdCore\CoreStarter\Http\Controllers\Dashboard\RoleTableController;
use IdCore\CoreStarter\Http\Controllers\Sistem\GroupController;
use IdCore\CoreStarter\Http\Controllers\Sistem\HakAksesController;
use IdCore\CoreStarter\Http\Controllers\Sistem\LogController;
use IdCore\CoreStarter\Http\Controllers\Sistem\MenuController;
use IdCore\CoreStarter\Http\Controllers\Sistem\SettingController;
use IdCore\CoreStarter\Http\Controllers\Sistem\UserController;
use Illuminate\Support\Facades\Route;

// Login: TIDAK butuh auth (justru untuk yang belum login)
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:login');
});

// Area privat: cukup 'auth', TIDAK ada core_permission di sini
// (karena LoginController/RoleSwitcherController bukan extends BaseCoreController)
Route::middleware(['web', 'auth', 'active'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/roles-json', [RoleTableController::class, 'index'])->name('dashboard.roles-json');
    Route::post('switch-role', [LoginController::class, 'switch'])->name('switch-role');
});

// Area admin RBAC: 'auth' DI SINI JUGA, core_permission MUNCUL OTOMATIS
// dari BaseCoreController::middleware() milik tiap controller
Route::middleware(['web', 'auth', 'active'])->prefix('sistem')->name('sistem.')->group(function () {
    Route::resource('group', GroupController::class);

    Route::get('user/ajax', [UserController::class, 'ajax'])->name('user.ajax');
    Route::resource('user', UserController::class);

    Route::resource('menu', MenuController::class);
    Route::resource('hak-akses', HakAksesController::class)
        ->only(['index', 'edit', 'update'])
        ->parameters(['hak-akses' => 'role']);
    Route::resource('setting', SettingController::class);
    Route::resource('log', LogController::class);
});
