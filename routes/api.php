<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ArtisanProductivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;

/**
 * ------------------------------------------------------------------------
 * auth routes
 * ------------------------------------------------------------------------
 */
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
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
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard route
    Route::get('dashboard', [DashboardController::class, 'index']);

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

    Route::match(['PUT', 'PATCH'], 'users/{userId}', [
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
    Route::get('artisans/productivity', [ArtisanProductivityController::class, 'index'])
        ->middleware('permission:artisans-all|artisans-view');
    Route::get('artisans/productivity/export', [ArtisanProductivityController::class, 'export'])
        ->middleware('permission:artisans-all|artisans-view');

    // Wildcard routes come after specific routes
    Route::get('artisans/{id}', [\App\Http\Controllers\ArtisanController::class, 'show'])
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
        Route::get('/report', [\App\Http\Controllers\AttendanceController::class, 'report'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::post('/bulk', [\App\Http\Controllers\AttendanceController::class, 'bulkStore'])
            ->middleware('permission:attendance-all|attendance-create');
        Route::get('/statistics', [\App\Http\Controllers\AttendanceController::class, 'statistics'])
            ->middleware('permission:attendance-all|attendance-view');

        // Existing attendance routes
        Route::get('/date/{date}', [\App\Http\Controllers\AttendanceController::class, 'getByDate'])
            ->middleware('permission:attendance-all|attendance-view');
        Route::put('/date/{date}', [\App\Http\Controllers\AttendanceController::class, 'updateByDate'])
            ->middleware('permission:attendance-all|attendance-edit');
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
        Route::get('/{id}/challan', [\App\Http\Controllers\OrderAssignmentController::class, 'downloadDispatchChallan'])
            ->middleware('permission:orders-all|orders-view');

        // Add new routes for update and delete
        Route::put('/{id}', [\App\Http\Controllers\OrderAssignmentController::class, 'update'])
            ->middleware('permission:orders-all|orders-edit');
        Route::delete('/{id}', [\App\Http\Controllers\OrderAssignmentController::class, 'destroy'])
            ->middleware('permission:orders-all|orders-delete');
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

        // Usage routes
        Route::get('/usage/summary/export', [\App\Http\Controllers\WoolUsageController::class, 'exportSummary'])
            ->middleware('permission:wool-all|wool-view');
        Route::get('/usage/summary', [\App\Http\Controllers\WoolUsageController::class, 'summary'])
            ->middleware('permission:wool-all|wool-view');
        Route::get('/usage/export', [\App\Http\Controllers\WoolUsageController::class, 'export'])
            ->middleware('permission:wool-all|wool-view');
        Route::get('/usage', [\App\Http\Controllers\WoolUsageController::class, 'index'])
            ->middleware('permission:wool-all|wool-view');
        Route::post('/usage', [\App\Http\Controllers\WoolUsageController::class, 'store'])
            ->middleware('permission:wool-all|wool-create');
        Route::get('/usage/{id}', [\App\Http\Controllers\WoolUsageController::class, 'show'])
            ->middleware('permission:wool-all|wool-view');
        Route::put('/usage/{id}', [\App\Http\Controllers\WoolUsageController::class, 'update'])
            ->middleware('permission:wool-all|wool-edit');
        Route::delete('/usage/{id}', [\App\Http\Controllers\WoolUsageController::class, 'destroy'])
            ->middleware('permission:wool-all|wool-delete');

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

    /**
     * ------------------------------------------------------------------------
     * wages routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('wages')->group(function () {
        Route::get('/calculations', [\App\Http\Controllers\WageController::class, 'calculations'])
            ->middleware('permission:wages-all|wages-view');
        Route::get('/export', [\App\Http\Controllers\WageController::class, 'export'])
            ->middleware('permission:wages-all|wages-view');
        Route::get('/report', [\App\Http\Controllers\WageController::class, 'report'])
            ->middleware('permission:wages-all|wages-view');
        Route::get('/report/export', [\App\Http\Controllers\WageController::class, 'exportReport'])
            ->middleware('permission:wages-all|wages-view');
    });

    /**
     * ------------------------------------------------------------------------
     * Inventory routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('inventory')->group(function () {
        // Raw Materials routes
        Route::get('/raw-materials', [\App\Http\Controllers\RawMaterialController::class, 'index'])
            ->middleware('permission:inventory-all|inventory-view');
        Route::post('/raw-materials', [\App\Http\Controllers\RawMaterialController::class, 'store'])
            ->middleware('permission:inventory-all|inventory-create');
        Route::get('/raw-materials/low-stock', [\App\Http\Controllers\RawMaterialController::class, 'getLowStock'])
            ->middleware('permission:inventory-all|inventory-view');
        Route::put('/raw-materials/{id}', [\App\Http\Controllers\RawMaterialController::class, 'update'])
            ->middleware('permission:inventory-all|inventory-edit');
        Route::delete('/raw-materials/{id}', [\App\Http\Controllers\RawMaterialController::class, 'destroy'])
            ->middleware('permission:inventory-all|inventory-delete');
        Route::post('/raw-materials/{id}/update-stock', [\App\Http\Controllers\RawMaterialController::class, 'updateStock'])
            ->middleware('permission:inventory-all|inventory-edit');

        // Products routes
        Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])
            ->middleware('permission:inventory-all|inventory-view');
        Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])
            ->middleware('permission:inventory-all|inventory-create');
        Route::get('/products/{id}', [\App\Http\Controllers\ProductController::class, 'show'])
            ->middleware('permission:inventory-all|inventory-view');
        Route::put('/products/{id}', [\App\Http\Controllers\ProductController::class, 'update'])
            ->middleware('permission:inventory-all|inventory-edit');
        Route::delete('/products/{id}', [\App\Http\Controllers\ProductController::class, 'destroy'])
            ->middleware('permission:inventory-all|inventory-delete');
        Route::post('/products/{id}/update-quantity', [\App\Http\Controllers\ProductController::class, 'updateQuantity'])
            ->middleware('permission:inventory-all|inventory-edit');
        Route::post('/products/{id}/update-status', [\App\Http\Controllers\ProductController::class, 'updateStatus'])
            ->middleware('permission:inventory-all|inventory-edit');
        Route::get('/products/inventory-summary', [\App\Http\Controllers\ProductController::class, 'getInventorySummary'])
            ->middleware('permission:inventory-all|inventory-view');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:sanctum');

    /**
     * ------------------------------------------------------------------------
     * payroll routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('payroll')->group(function () {
        // Admin Payroll
        Route::get('/admin', [\App\Http\Controllers\PayrollController::class, 'getAdminPayroll'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::post('/admin', [\App\Http\Controllers\PayrollController::class, 'saveAdminPayroll'])
            ->middleware('permission:payroll-all|payroll-create');

        // Worker Payroll
        Route::get('/worker', [\App\Http\Controllers\PayrollController::class, 'getWorkerPayroll'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::post('/worker', [\App\Http\Controllers\PayrollController::class, 'saveWorkerPayroll'])
            ->middleware('permission:payroll-all|payroll-create');

        // Artisan Advances
        Route::get('/advances', [\App\Http\Controllers\PayrollController::class, 'getAdvances'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::post('/advances', [\App\Http\Controllers\PayrollController::class, 'saveAdvances'])
            ->middleware('permission:payroll-all|payroll-create');
        Route::get('/advances/export', [\App\Http\Controllers\PayrollController::class, 'generateAdvanceReport'])
            ->middleware('permission:payroll-all|payroll-view')
            ->name('payroll.advances.export');

        // Salary Calculation Routes
        Route::get('/salary-calculation', [\App\Http\Controllers\PayrollController::class, 'getSalaryCalculations'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::post('/salary-calculation', [\App\Http\Controllers\PayrollController::class, 'saveSalaryCalculation'])
            ->middleware('permission:payroll-all|payroll-create');
        Route::get('/salary-calculation/export', [\App\Http\Controllers\PayrollController::class, 'exportSalaryCalculations'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/salary-calculation/{id}', [\App\Http\Controllers\PayrollController::class, 'getSalaryCalculation'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::put('/salary-calculation/{id}', [\App\Http\Controllers\PayrollController::class, 'updateSalaryCalculation'])
            ->middleware('permission:payroll-all|payroll-edit');
        Route::delete('/salary-calculation/{id}', [\App\Http\Controllers\PayrollController::class, 'deleteSalaryCalculation'])
            ->middleware('permission:payroll-all|payroll-delete');
        Route::get('/salary-calculation/department/{departmentId}', [\App\Http\Controllers\PayrollController::class, 'getDepartmentSalaryCalculations'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/salary-calculation/artisan/{artisanId}', [\App\Http\Controllers\PayrollController::class, 'getArtisanSalaryCalculations'])
            ->middleware('permission:payroll-all|payroll-view');

        // Bank Transfer
        Route::get('/bank-transfers', [\App\Http\Controllers\PayrollController::class, 'getBankTransfers'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/bank-transfer-sheet', [\App\Http\Controllers\PayrollController::class, 'generateBankTransferSheet'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/bank-transfer-csv', [\App\Http\Controllers\PayrollController::class, 'exportBankTransferCSV'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::post('/bank-transfer-email', [\App\Http\Controllers\PayrollController::class, 'sendBankPaymentEmail'])
            ->middleware('permission:payroll-all|payroll-view');

        // Cash Payment
        Route::get('/cash-payments', [\App\Http\Controllers\PayrollController::class, 'getCashPayments'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/cash-payment-sheet', [\App\Http\Controllers\PayrollController::class, 'generateCashPaymentSheet'])
            ->middleware('permission:payroll-all|payroll-view');
        Route::get('/cash-payment-csv', [\App\Http\Controllers\PayrollController::class, 'exportCashPaymentCSV'])
            ->middleware('permission:payroll-all|payroll-view');
    });

    // Reports Routes
    Route::get('/reports/orders', [ReportController::class, 'ordersReport']);
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport']);
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport']);

    /**
     * ------------------------------------------------------------------------
     * reports routes
     * ------------------------------------------------------------------------
     */
    Route::prefix('reports')->group(function () {
        // Attendance reports
        Route::get('/attendance/{subtype}', [ReportController::class, 'export'])
            ->middleware('permission:reports-all|reports-view');

        // Inventory reports
        Route::get('/inventory/{subtype}', [ReportController::class, 'export'])
            ->middleware('permission:reports-all|reports-view');

        // Orders reports
        Route::get('/orders/{subtype}', [ReportController::class, 'export'])
            ->middleware('permission:reports-all|reports-view');

        // Financial reports
        Route::get('/financial/{subtype}', [ReportController::class, 'export'])
            ->middleware('permission:reports-all|reports-view');

        // Wool management reports
        Route::get('/wool/{subtype}', [ReportController::class, 'export'])
            ->middleware('permission:reports-all|reports-view');
    });

    Route::get('reports/{type}/{subtype}', [ReportController::class, 'downloadReport'])
        ->middleware('permission:reports-all|reports-view');
});
