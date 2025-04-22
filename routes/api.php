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

    // IMPORTANT: Specific routes must come before wildcard routes
    Route::get('artisans/export', [\App\Http\Controllers\ArtisanController::class, 'export'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::post('artisans/import', [\App\Http\Controllers\ArtisanController::class, 'import'])
        ->middleware('permission:artisans-all|artisans-create');

    // Wildcard routes come after specific routes
    Route::get('artisans/{artisanId}', [\App\Http\Controllers\ArtisanController::class, 'show'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::post('artisans', [\App\Http\Controllers\ArtisanController::class, 'store'])
        ->middleware('permission:artisans-all|artisans-create');
    Route::match(['PUT', 'PATCH'], 'artisans/{id}', [\App\Http\Controllers\ArtisanController::class, 'update'])
        ->middleware('permission:artisans-all|artisans-edit');
    Route::delete('artisans/{artisanId}', [\App\Http\Controllers\ArtisanController::class, 'destroy'])
        ->middleware('permission:artisans-all|artisans-delete');

    /**
     * ------------------------------------------------------------------------
     * attendance routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('attendance')->group(function () {
        // Main attendance routes
        Route::get('/', [\App\Http\Controllers\AttendanceController::class, 'index'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::post('/', [\App\Http\Controllers\AttendanceController::class, 'store'])
            ->middleware('permission:attendance-all|attendance-create');
        Route::get('/{id}', [\App\Http\Controllers\AttendanceController::class, 'show'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::patch('/{id}', [\App\Http\Controllers\AttendanceController::class, 'update'])
            ->middleware('permission:attendance-all|attendance-edit');
        Route::delete('/{id}', [\App\Http\Controllers\AttendanceController::class, 'destroy'])
            ->middleware('permission:attendance-all|attendance-delete');

        // Check in/out routes
        Route::post('/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])
            ->middleware('permission:attendance-all|attendance-create');
        Route::post('/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])
            ->middleware('permission:attendance-all|attendance-edit');

        // Reports routes
        Route::get('/reports/daily', [\App\Http\Controllers\AttendanceController::class, 'dailyReport'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::get('/reports/monthly', [\App\Http\Controllers\AttendanceController::class, 'monthlyReport'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::get('/reports/export', [\App\Http\Controllers\AttendanceController::class, 'export'])
            ->middleware('permission:attendance-all|attendance-view');
    });

    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index']);
    Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store']);
    Route::get('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'show']);
    Route::patch('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy']);

    /**
     * ------------------------------------------------------------------------
     * petty cash routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('petty-cash')->group(function () {
        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [\App\Http\Controllers\PettyCashCategoryController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\PettyCashCategoryController::class, 'store']);
            Route::get('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'show']);
            Route::put('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'update']);
            Route::delete('/{category}', [\App\Http\Controllers\PettyCashCategoryController::class, 'destroy']);
        });

        // Main routes
        Route::get('/', [\App\Http\Controllers\PettyCashController::class, 'index']);
        Route::get('/dashboard', [\App\Http\Controllers\PettyCashController::class, 'dashboard']);
        Route::post('/', [\App\Http\Controllers\PettyCashController::class, 'store']);

        // IMPORTANT: Specific routes must come before wildcard routes
        Route::get('/export', [\App\Http\Controllers\PettyCashController::class, 'export']);
        Route::post('/import', [\App\Http\Controllers\PettyCashController::class, 'import']);

        // PDF Reports
        Route::post('/reports/pdf/download', [\App\Http\Controllers\PettyCashController::class, 'generatePdfReport']);
        Route::post('/reports/pdf/preview', [\App\Http\Controllers\PettyCashController::class, 'previewPdfReport']);

        // Wildcard routes come after specific routes
        Route::get('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'show']);
        Route::put('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'update']);
        Route::delete('/{transaction}', [\App\Http\Controllers\PettyCashController::class, 'destroy']);
    });

    /**
     * ------------------------------------------------------------------------
     * orders routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\OrderController::class, 'index'])
            ->middleware('permission:orders-all|orders-view');
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'store'])
            ->middleware('permission:orders-all|orders-create');
        Route::get('/available-artisans', [\App\Http\Controllers\OrderController::class, 'getAvailableArtisans'])
            ->middleware('permission:orders-all|orders-view');
        Route::get('/{id}', [\App\Http\Controllers\OrderController::class, 'show'])
            ->middleware('permission:orders-all|orders-view');
        Route::put('/{id}', [\App\Http\Controllers\OrderController::class, 'update'])
            ->middleware('permission:orders-all|orders-edit');
        Route::patch('/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])
            ->middleware('permission:orders-all|orders-edit');
        Route::delete('/{id}', [\App\Http\Controllers\OrderController::class, 'destroy'])
            ->middleware('permission:orders-all|orders-delete');
    });

    /**
     * ------------------------------------------------------------------------
     * order assignments routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('order-assignments')->group(function () {
        Route::get('/', [\App\Http\Controllers\OrderAssignmentController::class, 'index'])
            ->middleware('permission:orders-all|orders-view');
        Route::post('/assign', [\App\Http\Controllers\OrderAssignmentController::class, 'assign'])
            ->middleware('permission:orders-all|orders-edit');
        Route::patch('/{id}/complete', [\App\Http\Controllers\OrderAssignmentController::class, 'markCompleted'])
            ->middleware('permission:orders-all|orders-edit');
        Route::patch('/{id}/approve-reject', [\App\Http\Controllers\OrderAssignmentController::class, 'approveOrReject'])
            ->middleware('permission:orders-all|orders-edit');
        Route::patch('/{id}/dispatch', [\App\Http\Controllers\OrderAssignmentController::class, 'markDispatched'])
            ->middleware('permission:orders-all|orders-edit');
        Route::post('/bulk-approve', [\App\Http\Controllers\OrderAssignmentController::class, 'bulkApprove'])
            ->middleware('permission:orders-all|orders-edit');
        Route::post('/bulk-dispatch', [\App\Http\Controllers\OrderAssignmentController::class, 'bulkDispatch'])
            ->middleware('permission:orders-all|orders-edit');
        // Add new route for downloading dispatch challan
        Route::get('/{id}/challan', [\App\Http\Controllers\OrderAssignmentController::class, 'downloadDispatchChallan'])
            ->middleware('permission:orders-all|orders-view');
    });

    // Add the missing route for order assignments
    Route::post('/orders/{id}/assignments', [\App\Http\Controllers\OrderAssignmentController::class, 'assignToOrder'])
        ->middleware('permission:orders-all|orders-edit');

    /**
     * ------------------------------------------------------------------------
     * Order routes
     * ------------------------------------------------------------------------
     */
    // Order routes
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
    Route::put('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'update']);
    Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'destroy']);
    Route::get('/orders/{id}/available-artisans', [\App\Http\Controllers\OrderController::class, 'getAvailableArtisans']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus']);

    /**
     * ------------------------------------------------------------------------
     * wool management routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('wool')->group(function () {
        // Suppliers routes
        Route::get('/suppliers', [\App\Http\Controllers\WoolSupplierController::class, 'index'])
            ->middleware('permission:wool-all|wool-view');
        Route::post('/suppliers', [\App\Http\Controllers\WoolSupplierController::class, 'store'])
            ->middleware('permission:wool-all|wool-create');
        Route::get('/suppliers/{id}', [\App\Http\Controllers\WoolSupplierController::class, 'show'])
            ->middleware('permission:wool-all|wool-view');
        Route::put('/suppliers/{id}', [\App\Http\Controllers\WoolSupplierController::class, 'update'])
            ->middleware('permission:wool-all|wool-edit');
        Route::delete('/suppliers/{id}', [\App\Http\Controllers\WoolSupplierController::class, 'destroy'])
            ->middleware('permission:wool-all|wool-delete');
        Route::post('/suppliers/{id}/restore', [\App\Http\Controllers\WoolSupplierController::class, 'restore'])
            ->middleware('permission:wool-all|wool-edit');

        // Orders routes
        Route::get('/orders', [\App\Http\Controllers\WoolOrderController::class, 'index'])
            ->middleware('permission:wool-all|wool-view');
        Route::post('/orders', [\App\Http\Controllers\WoolOrderController::class, 'store'])
            ->middleware('permission:wool-all|wool-create');
        Route::get('/orders/{id}', [\App\Http\Controllers\WoolOrderController::class, 'show'])
            ->middleware('permission:wool-all|wool-view');
        Route::put('/orders/{id}', [\App\Http\Controllers\WoolOrderController::class, 'update'])
            ->middleware('permission:wool-all|wool-edit');
        Route::delete('/orders/{id}', [\App\Http\Controllers\WoolOrderController::class, 'destroy'])
            ->middleware('permission:wool-all|wool-delete');

        // Stock routes
        Route::get('/stock', [\App\Http\Controllers\WoolStockController::class, 'index'])
            ->middleware('permission:wool-all|wool-view');
        Route::put('/stock/{id}', [\App\Http\Controllers\WoolStockController::class, 'update'])
            ->middleware('permission:wool-all|wool-edit');
        Route::delete('/stock/{id}', [\App\Http\Controllers\WoolStockController::class, 'destroy'])
            ->middleware('permission:wool-all|wool-delete');
        Route::post('/stock/export', [\App\Http\Controllers\WoolStockController::class, 'export'])
            ->middleware('permission:wool-all|wool-view');
    });

    // Wage Calculation Routes
    Route::prefix('wages')->group(function () {
        Route::get('/calculations', [\App\Http\Controllers\WageController::class, 'calculations']);
        Route::post('/export', [\App\Http\Controllers\WageController::class, 'export']);
    });
});
