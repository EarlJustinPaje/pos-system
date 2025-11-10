<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;

// Home page (protected)
Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

// Guest routes (only if NOT logged in)
Route::middleware('guest')->group(function () {
    // Login - FIXED: Remove duplicate routes
    Route::get('/login', [LoginController::class, 'show'])->name('login'); // Laravel expects this name
    Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
    
    // Register - Using register.show as the primary name since views expect it
    Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.perform');
});

// Authenticated routes (only if logged in)
Route::middleware('auth')->group(function () {
    // Dashboard chart data
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])
        ->name('dashboard.chart-data');
    
    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/deactivate', [ProductController::class, 'deactivate'])
        ->name('products.deactivate');
    
    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [POSController::class, 'searchProducts'])->name('pos.search');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    
    // Reports
    Route::get('/reports/sales', [ReportsController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/item-sales', [ReportsController::class, 'itemSalesReport'])->name('reports.item-sales');
    Route::get('/reports/inventory', [ReportsController::class, 'inventoryReport'])->name('reports.inventory');
    
    // User Management
    Route::resource('users', UserController::class)->except(['create', 'store', 'destroy']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    
    // Audit (Admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });
    
    // Logout
    Route::post('/logout', [LogoutController::class, 'perform'])->name('logout');
});