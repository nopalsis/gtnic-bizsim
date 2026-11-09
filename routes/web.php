<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

// Authenticated Routes Protected with Role-Based Authorization
Route::middleware('auth')->group(function () {
    // Logout Action
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Root redirect to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // 1. Dashboard (Accessible to all authenticated staff: Superadmin, Admin, Sales, Craftsman)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:superadmin,admin,sales,craftsman')
        ->name('dashboard');

    // 2. Sales Intelligence & Deal Price History (Accessible to Superadmin, Admin & Sales Force)
    Route::middleware('role:superadmin,admin,sales')->group(function () {
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    });

    // 3. Inventory & Leather Waste Tracker
    // Read catalog & stock check: Superadmin, Admin, Sales, Craftsman
    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware('role:superadmin,admin,sales,craftsman')
        ->name('inventory.index');

    // Production floor operations (Waste log & stock movements): Superadmin, Admin & Craftsman
    Route::middleware('role:superadmin,admin,craftsman')->group(function () {
        Route::post('/inventory/waste', [InventoryController::class, 'recordWaste'])->name('inventory.waste');
        Route::post('/inventory/in', [InventoryController::class, 'recordProductionIn'])->name('inventory.in');
        Route::post('/inventory/mutation', [InventoryController::class, 'recordMutation'])->name('inventory.mutation');
    });

    // Master Product Catalog CRUD: Superadmin & Admin
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::post('/inventory/product', [InventoryController::class, 'storeProduct'])->name('inventory.product.store');
        Route::put('/inventory/product/{product}', [InventoryController::class, 'updateProduct'])->name('inventory.product.update');
        Route::delete('/inventory/product/{product}', [InventoryController::class, 'destroyProduct'])->name('inventory.product.destroy');

        // Master Product Categories CRUD
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // 4. What-If Scenario Simulator (Superadmin Only - Exposes Profit Projections & Financial Margins)
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator.index');
        Route::post('/simulator/settings', [SimulatorController::class, 'updateSettings'])->name('simulator.settings');
    });

    // 5. Team & User Management (Superadmin Only - Exposes Staff Salaries & Payroll Settings)
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/export', [UserController::class, 'exportCsv'])->name('users.export');
    });
});
