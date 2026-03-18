<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [ApiController::class, 'login']);

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/auth/logout', [ApiController::class, 'logout']);
    Route::get('/products', [ApiController::class, 'getProducts']);
    Route::get('/customers', [ApiController::class, 'getCustomers']);
    Route::get('/sales', [ApiController::class, 'getSales']);
    Route::get('/expenses', [ApiController::class, 'getExpenses']);
    Route::post('/sync', [ApiController::class, 'sync']);

    // Admin-only Routes
    Route::prefix('admin')->group(function () {
        Route::get('/users', [ApiController::class, 'getUsers']);
        Route::get('/purchases', [ApiController::class, 'getPurchases']);
        Route::get('/suppliers', [ApiController::class, 'getSuppliers']);
        Route::get('/inventories', [ApiController::class, 'getInventories']);
        Route::get('/stats', [ApiController::class, 'getAdminStats']);
        Route::get('/roles', [ApiController::class, 'getRoles']);

        Route::post('/users', [ApiController::class, 'storeUser']);
        Route::patch('/users/{id}/toggle', [ApiController::class, 'toggleUser']);

        Route::post('/products', [ApiController::class, 'storeProduct']);
        Route::put('/products/{id}', [ApiController::class, 'updateProduct']);
    });
});
