<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [ApiController::class, 'login']);

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/auth/logout', [ApiController::class, 'logout']);
    Route::get('/products', [ApiController::class, 'getProducts']);
    Route::get('/customers', [ApiController::class, 'getCustomers']);
    Route::post('/sync', [ApiController::class, 'sync']);
});
