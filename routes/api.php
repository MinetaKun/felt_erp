<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PettyCashController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/users', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{employee_id}', [EmployeeController::class, 'show']);

// Show all orders with assignments
Route::get('/orders', [OrderController::class, 'index']);

// Create a new order
Route::post('/orders', [OrderController::class, 'store']);

// Assign an artisan to an order
Route::post('/orders/{orderId}/assign', [OrderController::class, 'assignArtisan']);

Route::prefix('petty-cash')->group(function () {
    Route::get('/', [PettyCashController::class, 'index']);
    Route::post('/transactions', [PettyCashController::class, 'store']);
    Route::get('/{id}', [PettyCashController::class, 'show']);
    Route::put('/update/{id}', [PettyCashController::class, 'update']);
    Route::delete('/delete/{id}', [PettyCashController::class, 'destroy']);
    Route::get('/monthly-report', [PettyCashController::class, 'monthlyReport']);
});
