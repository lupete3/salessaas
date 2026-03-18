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
        Route::post('/purchases', [ApiController::class, 'storePurchase']);
        Route::patch('/purchases/{id}/pay', [ApiController::class, 'payPurchase']);
        Route::get('/suppliers', [ApiController::class, 'getSuppliers']);
        Route::post('/suppliers', [ApiController::class, 'storeSupplier']);
        Route::put('/suppliers/{id}', [ApiController::class, 'updateSupplier']);
        Route::delete('/suppliers/{id}', [ApiController::class, 'destroySupplier']);
        Route::get('/inventories', [ApiController::class, 'getInventories']);
        Route::get('/stats', [ApiController::class, 'getAdminStats']);
        Route::get('/roles', [ApiController::class, 'getRoles']);

        Route::post('/users', [ApiController::class, 'storeUser']);
        Route::put('/users/{id}', [ApiController::class, 'updateUser']);
        Route::patch('/users/{id}/toggle', [ApiController::class, 'toggleUser']);

        Route::post('/products', [ApiController::class, 'storeProduct']);
        Route::put('/products/{id}', [ApiController::class, 'updateProduct']);
    });
});
