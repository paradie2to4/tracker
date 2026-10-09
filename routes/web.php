<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchRecallController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStatusController;
use Illuminate\Support\Facades\Route;

// Login and logout routes are registered by Laravel Fortify.

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class)->except('destroy');
    Route::patch('products/{product}/status', ProductStatusController::class)->name('products.status');

    Route::resource('batches', BatchController::class)->except('destroy');
    Route::post('batches/{batch}/recall', BatchRecallController::class)->name('batches.recall');
});
