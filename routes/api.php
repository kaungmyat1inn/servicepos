<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DeviceController;
use App\Http\Controllers\Tenant\TicketController;
use App\Http\Controllers\Tenant\TicketStatusController;
use Illuminate\Support\Facades\Route;

// Admin Authentication Routes (no auth required)
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
});

// Admin Routes (requires token auth)
Route::prefix('admin')->middleware(['admin.auth.token'])->group(function () {
    // Auth routes
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('me', [AdminAuthController::class, 'me']);

    // Shop management
    Route::get('shops', [AdminShopController::class, 'index']);
    Route::post('shops', [AdminShopController::class, 'store']);
});

Route::middleware(['tenant.api.key'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'summary']);

    Route::get('customers', [CustomerController::class, 'index']);
    Route::post('customers', [CustomerController::class, 'store']);
    Route::get('customers/{customer}', [CustomerController::class, 'show']);
    Route::put('customers/{customer}', [CustomerController::class, 'update']);

    Route::get('devices', [DeviceController::class, 'index']);
    Route::post('devices', [DeviceController::class, 'store']);
    Route::get('devices/{device}', [DeviceController::class, 'show']);
    Route::put('devices/{device}', [DeviceController::class, 'update']);

    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('tickets/{ticket}', [TicketController::class, 'update']);

    Route::get('ticket-statuses', [TicketStatusController::class, 'index']);
    Route::post('ticket-statuses', [TicketStatusController::class, 'store']);
    Route::get('ticket-statuses/{status}', [TicketStatusController::class, 'show']);
    Route::put('ticket-statuses/{status}', [TicketStatusController::class, 'update']);
    Route::delete('ticket-statuses/{status}', [TicketStatusController::class, 'destroy']);
});
