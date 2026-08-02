<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['web', 'auth:customer', 'active:customer'])->group(function () {
    
});
