<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('{any}', function () {
    return view('app'); // Main Blade view for Vue app
})->where('any', '.*');


// Show login form (optional for web-based apps)

// Handle login request
Route::post('login', [AuthController::class, 'login']);

// Handle logout request
Route::post('logout', [AuthController::class, 'logout']);


Route::post('/user-registration', [UserController::class, 'registerUser']);

Route::get('/users', [UserController::class, 'getAllUsers']);

Route::get('/order-management');

Route::get('/artisan-management');

Route::get('/payroll-wages');

Route::get('/petty-cash');
