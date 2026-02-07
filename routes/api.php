<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;

// API v1
Route::prefix('v1')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {

        // Products
        Route::apiResource('products', ProductController::class);

        // Customers CRUD
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']); // list
            Route::post('/', [CustomerController::class, 'store']); // create
            Route::put('/{id}', [CustomerController::class, 'update']); // update
            Route::delete('/{id}', [CustomerController::class, 'destroy']); // soft delete
        });

       
    });
});

