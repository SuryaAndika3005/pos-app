<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ===================== AUTENTIKASI =====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ===================== AREA TERPROTEKSI =====================
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/pos');

    // --- Kasir POS ---
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');
    Route::patch('/pos/queue/{transaction}/complete', [PosController::class, 'complete'])->name('pos.complete');

    // --- Edit Nota (kasir & admin boleh edit, tapi bisa dibatasi hanya admin di sini) ---
    Route::get('/pos/transactions/{transaction}/edit', [PosController::class, 'edit'])->name('pos.edit');
    Route::put('/pos/transactions/{transaction}', [PosController::class, 'update'])->name('pos.update');

    // --- Admin only ---
    Route::middleware('admin')->group(function () {

        // Gudang
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
            Route::get('/{product}/history', [InventoryController::class, 'history'])->name('history');
            Route::post('/{product}/restock', [InventoryController::class, 'restock'])->name('restock');
        });

        // Laporan
        Route::prefix('report')->name('report.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/{transaction}', [ReportController::class, 'show'])->name('show');
        });

        // Direktori Pelanggan
        Route::prefix('customers')->name('customer.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/search', [CustomerController::class, 'search'])->name('search');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy'); // FIX: tambah route hapus
            Route::post('/{customer}/pay-all', [DebtController::class, 'payAll'])->name('pay-all');
        });

        // Manajemen Utang
        Route::prefix('debts')->name('debt.')->group(function () {
            Route::get('/', [DebtController::class, 'index'])->name('index');
            Route::get('/{debt}', [DebtController::class, 'show'])->name('show');
            Route::post('/{debt}/pay', [DebtController::class, 'pay'])->name('pay');
        });
    });
});