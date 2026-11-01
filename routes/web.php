<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 1. Executive Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// 2. Sales Intelligence & Deal Price History
Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');

// 3. Inventory & Waste Tracker
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory/waste', [InventoryController::class, 'recordWaste'])->name('inventory.waste');
Route::post('/inventory/in', [InventoryController::class, 'recordProductionIn'])->name('inventory.in');

// 4. What-If Scenario Simulator
Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator.index');
Route::post('/simulator/settings', [SimulatorController::class, 'updateSettings'])->name('simulator.settings');

// 5. Team & User Management (Staff Directory)
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::get('/users/export', [UserController::class, 'exportCsv'])->name('users.export');
