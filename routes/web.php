<?php

use App\Http\Controllers\AuthController;
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

    // 1. Executive Dashboard (Accessible to all authenticated staff: Admin, Sales, Craftsman)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:admin,sales,craftsman')
        ->name('dashboard');

    // 2. Sales Intelligence & Deal Price History (Accessible to Admin & Sales Force)
    Route::middleware('role:admin,sales')->group(function () {
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    });

    // 3. Inventory & Leather Waste Tracker
    // Read catalog & stock check: Admin, Sales, Craftsman
    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware('role:admin,sales,craftsman')
        ->name('inventory.index');

    // Production floor operations (Waste log & stock movements): Admin & Craftsman
    Route::middleware('role:admin,craftsman')->group(function () {
        Route::post('/inventory/waste', [InventoryController::class, 'recordWaste'])->name('inventory.waste');
        Route::post('/inventory/in', [InventoryController::class, 'recordProductionIn'])->name('inventory.in');
        Route::post('/inventory/mutation', [InventoryController::class, 'recordMutation'])->name('inventory.mutation');
    });

    // Master Product Catalog CRUD: Admin Only
    Route::middleware('role:admin')->group(function () {
        Route::post('/inventory/product', [InventoryController::class, 'storeProduct'])->name('inventory.product.store');
        Route::put('/inventory/product/{product}', [InventoryController::class, 'updateProduct'])->name('inventory.product.update');
        Route::delete('/inventory/product/{product}', [InventoryController::class, 'destroyProduct'])->name('inventory.product.destroy');
    });

    // 4. What-If Scenario Simulator (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator.index');
        Route::post('/simulator/settings', [SimulatorController::class, 'updateSettings'])->name('simulator.settings');
    });

    // 5. Team & User Management (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/export', [UserController::class, 'exportCsv'])->name('users.export');
    });
});
