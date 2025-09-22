<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderStatusController;
use App\Http\Controllers\API\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes (require token)
|--------------------------------------------------------------------------
*/

Route::middleware(['api.rate_limit'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['api.rate_limit', 'api.auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::apiResource('order-statuses', OrderStatusController::class)->only(['index']);
    
    // Dashboard routes
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/order-status-summary', [DashboardController::class, 'orderStatusSummary']);
});
