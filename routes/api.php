<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ------------------------------------------------------------------------
 * auth routes
 * ------------------------------------------------------------------------
 */
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::get('auth/verify', [
    \App\Http\Controllers\AuthController::class,
    'verify',
]);

Route::middleware('auth:sanctum')->group(function () {
    /**
     * ------------------------------------------------------------------------
     * common routes
     * ------------------------------------------------------------------------
     */
    Route::get('logout', [
        \App\Http\Controllers\AuthController::class,
        'logout',
    ]);
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    /**
     * ------------------------------------------------------------------------
     * users routes
     * ------------------------------------------------------------------------
     */
    Route::get('users', [
        \App\Http\Controllers\UserController::class,
        'index',
    ])->middleware('permission:users-all|users-view');

    Route::post('users', [
        \App\Http\Controllers\UserController::class,
        'store',
    ])->middleware('permission:users-all|users-create');

    Route::patch('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'update',
    ])->middleware('permission:users-all|users-edit');

    Route::delete('users/{userId}', [
        \App\Http\Controllers\UserController::class,
        'destroy',
    ])->middleware('permission:users-all|users-delete');

    /**
     * ------------------------------------------------------------------------
     * roles routes
     * ------------------------------------------------------------------------
     */
    Route::get('roles', [
        \App\Http\Controllers\RoleController::class,
        'index',
    ])->middleware('permission:roles-all|roles-view');

    Route::post('roles', [
        \App\Http\Controllers\RoleController::class,
        'store',
    ])->middleware('permission:roles-all|roles-create');

    Route::patch('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'update',
    ])->middleware('permission:roles-all|roles-edit');

    Route::delete('roles/{roleId}', [
        \App\Http\Controllers\RoleController::class,
        'destroy',
    ])->middleware('permission:roles-all|roles-delete');

    /**
     * ------------------------------------------------------------------------
     * permissions routes
     * ------------------------------------------------------------------------
     */
    Route::get('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'index',
    ])->middleware('permission:permissions-all|permissions-view');

    Route::post('permissions', [
        \App\Http\Controllers\PermissionController::class,
        'store',
    ])->middleware('permission:permissions-all|permissions-create');

    Route::patch('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'update',
    ])->middleware('permission:permissions-all|permissions-edit');

    Route::delete('permissions/{permissionId}', [
        \App\Http\Controllers\PermissionController::class,
        'destroy',
    ])->middleware('permission:permissions-all|permissions-delete');

    /**
     * ------------------------------------------------------------------------
     * artisans routes
     * ------------------------------------------------------------------------
     */
    Route::get('artisans', [\App\Http\Controllers\ArtisanController::class, 'index'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::get('artisans/{artisanId}', [\App\Http\Controllers\ArtisanController::class, 'show'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::post('artisans', [\App\Http\Controllers\ArtisanController::class, 'store'])
        ->middleware('permission:artisans-all|artisans-create');
    Route::match(['PUT', 'PATCH'], 'artisans/{id}', [\App\Http\Controllers\ArtisanController::class, 'update'])
        ->middleware('permission:artisans-all|artisans-edit');
    Route::delete('artisans/{artisanId}', [\App\Http\Controllers\ArtisanController::class, 'destroy'])
        ->middleware('permission:artisans-all|artisans-delete');
    Route::get('artisans/export', [\App\Http\Controllers\ArtisanController::class, 'export'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::post('artisans/import', [\App\Http\Controllers\ArtisanController::class, 'import']);

    /**
     * ------------------------------------------------------------------------
     * attendance routes (nested under artisans)
     * ------------------------------------------------------------------------
     */
    Route::get('artisans/{artisanId}/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])
        ->middleware('permission:attendance-all|attendance-view');
    Route::post('artisans/{artisanId}/attendance', [\App\Http\Controllers\AttendanceController::class, 'store'])
        ->middleware('permission:attendance-all|attendance-create');
    Route::patch('attendance/{attendanceId}', [\App\Http\Controllers\AttendanceController::class, 'update'])
        ->middleware('permission:attendance-all|attendance-edit');
    Route::delete('attendance/{attendanceId}', [\App\Http\Controllers\AttendanceController::class, 'destroy'])
        ->middleware('permission:attendance-all|attendance-delete');

    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index']);
    Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store']);
    Route::get('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'show']);
    Route::patch('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy']);



    Route::prefix('petty-cash')->group(function () {
        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [\App\Http\Controllers\PettyCashCategoryController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\PettyCashCategoryController::class, 'store']);
            Route::get('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'show']);
            Route::put('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'update']);
            Route::delete('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'destroy']);
        });

        // Transactions
        Route::get('/', [\App\Http\Controllers\PettyCashController::class, 'index']);
        Route::get('/dashboard', [\App\Http\Controllers\PettyCashController::class, 'dashboard']);
        Route::post('/', [\App\Http\Controllers\PettyCashController::class, 'store']);
        Route::get('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'show']);
        Route::put('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'update']);
        Route::delete('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'destroy']);

        // Export/Import
        Route::get('/export', [\App\Http\Controllers\PettyCashController::class, 'export']);
        Route::post('/import', [\App\Http\Controllers\PettyCashController::class, 'import']);

        // PDF Reports
        Route::post('/reports/pdf/download', [\App\Http\Controllers\PettyCashController::class, 'generatePdfReport']);
        Route::post('/reports/pdf/preview', [\App\Http\Controllers\PettyCashController::class, 'previewPdfReport']);
    });
});
