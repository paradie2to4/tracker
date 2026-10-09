<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchRecallController;
use App\Http\Controllers\BatchStockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStatusController;
use App\Http\Controllers\ShipmentCancellationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentReceiptController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Login, logout and registration routes are registered by Laravel Fortify.

Route::get('/', WelcomeController::class)->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class)->except('destroy');
    Route::patch('products/{product}/status', ProductStatusController::class)->name('products.status');

    Route::resource('batches', BatchController::class)->except('destroy');
    Route::post('batches/{batch}/recall', BatchRecallController::class)->name('batches.recall');
    Route::post('batches/{batch}/removals', [BatchStockController::class, 'remove'])->name('batches.removals.store');
    Route::post('batches/{batch}/opening-stock', [BatchStockController::class, 'assignOpening'])->name('batches.opening-stock.store');

    // Supply-chain master data. No destroy routes: organisations and
    // locations that appear in the ledger are deactivated, never deleted.
    Route::resource('organizations', OrganizationController::class)->except('destroy');
    Route::resource('organizations.locations', LocationController::class)
        ->shallow()
        ->only(['create', 'store', 'show', 'edit', 'update']);

    // Shipments are never edited: they only move between statuses.
    Route::resource('shipments', ShipmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('shipments/{shipment}/receipt', ShipmentReceiptController::class)->name('shipments.receipt.store');
    Route::post('shipments/{shipment}/cancellation', ShipmentCancellationController::class)->name('shipments.cancellation.store');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit.index');
});
