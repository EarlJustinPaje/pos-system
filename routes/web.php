<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ForecastingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PaymentMethodController;

// ------------------------------------------
// Public / Guest Routes
// ------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

    Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.perform');
});

// ------------------------------------------
// Authenticated Routes
// ------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Home / Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    // --------------------------------------
    // POS
    // --------------------------------------
    Route::prefix('pos')->name('pos.')->middleware('permission:view_pos')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/search-products', [POSController::class, 'searchProducts'])->name('search-products');
        Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout');
        Route::get('/receipt/{sale}', [POSController::class, 'printReceipt'])->name('receipt');
        Route::post('/search-barcode', [POSController::class, 'searchByBarcode'])->name('search-barcode');
        Route::post('/calculate-price', [POSController::class, 'calculatePrice'])->name('calculate-price');
    });

    // --------------------------------------
    // Product Management
    // --------------------------------------
    Route::post('products/search-by-barcode', [ProductController::class, 'searchByBarcode'])->name('products.search-by-barcode');
    Route::get('products/{product}/print-barcode', [ProductController::class, 'printBarcode'])->name('products.print-barcode');
    Route::get('products/{product}/print-qrcode', [ProductController::class, 'printQRCode'])->name('products.print-qrcode');
    Route::post('products/bulk-print-barcodes', [ProductController::class, 'bulkPrintBarcodes'])->name('products.bulk-print-barcodes');
    Route::patch('products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');
    Route::resource('products', ProductController::class)->middleware('permission:view_products');

    // Product Import
    Route::prefix('products/imports')->name('products.imports.')
        ->middleware('permission:import_products')->group(function () {
        Route::get('/', [ProductImportController::class, 'index'])->name('index');
        Route::get('/create', [ProductImportController::class, 'create'])->name('create');
        Route::post('/', [ProductImportController::class, 'store'])->name('store');
        Route::get('/{import}', [ProductImportController::class, 'show'])->name('show');
        Route::get('/{import}/download', [ProductImportController::class, 'download'])->name('download');
        Route::get('/{import}/retry', [ProductImportController::class, 'retry'])->name('retry');
        Route::delete('/{import}', [ProductImportController::class, 'destroy'])->name('destroy');
        Route::get('/{import}/progress', [ProductImportController::class, 'getProgress'])->name('progress');
        Route::get('/template/download', [ProductImportController::class, 'downloadTemplate'])->name('download-template');
    });

    // --------------------------------------
    // Categories / Suppliers / Promotions / Branches / Payment Methods
    // --------------------------------------
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('categories', CategoryController::class)->middleware('permission:manage_categories');

    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::resource('suppliers', SupplierController::class)->middleware('permission:manage_suppliers');

    Route::patch('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggle-status');
    Route::resource('promotions', PromotionController::class)->middleware('permission:manage_promotions');

    Route::patch('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
    Route::patch('branches/{branch}/update-markup', [BranchController::class, 'updateMarkup'])->name('branches.update-markup');
    Route::resource('branches', BranchController::class)->middleware('permission:manage_branches');

    Route::patch('payment-methods/{paymentMethod}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');
    Route::resource('payment-methods', PaymentMethodController::class)->middleware('permission:manage_payment_methods');

    // --------------------------------------
    // Forecasting & Reordering
    // --------------------------------------
    Route::prefix('forecasting')->name('forecasting.')->middleware('permission:view_forecasting')->group(function () {
        Route::get('/', [ForecastingController::class, 'index'])->name('index');
        Route::get('/product/{product}', [ForecastingController::class, 'show'])->name('show');
        Route::post('/generate', [ForecastingController::class, 'generate'])->name('generate');
        Route::get('/reorder-alerts', [ForecastingController::class, 'reorderAlerts'])->name('reorder-alerts');
        Route::post('/alerts/generate', [ForecastingController::class, 'generateReorderAlerts'])->name('generate-alerts');
        Route::patch('/alerts/{alert}/acknowledge', [ForecastingController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
        Route::patch('/alerts/{alert}/ordered', [ForecastingController::class, 'markAlertAsOrdered'])->name('alerts.ordered');
        Route::patch('/alerts/{alert}/resolve', [ForecastingController::class, 'resolveAlert'])->name('alerts.resolve');
    });

    // --------------------------------------
    // Reports
    // --------------------------------------
    Route::get('/reports/sales', [ReportsController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/item-sales', [ReportsController::class, 'itemSalesReport'])->name('reports.item-sales');
    Route::get('/reports/inventory', [ReportsController::class, 'inventoryReport'])->name('reports.inventory');

    // --------------------------------------
    // User Management
    // --------------------------------------
    Route::resource('users', UserController::class)->except(['create', 'store', 'destroy']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

    // --------------------------------------
    // Audit (Admin Only)
    // --------------------------------------
    Route::middleware('admin')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });

    // --------------------------------------
    // Notifications
    // --------------------------------------
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::patch('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // --------------------------------------
    // Logout
    // --------------------------------------
    Route::post('/logout', [LogoutController::class, 'perform'])->name('logout');
});
