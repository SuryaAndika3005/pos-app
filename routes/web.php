<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — BuildPro POS
|--------------------------------------------------------------------------
*/

// ===================== AUTENTIKASI =====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ===================== AREA TERPROTEKSI =====================
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/pos');

    // --- Kasir POS (semua role login boleh akses) ---
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');

    // --- Gudang & Laporan (khusus admin) ---
    Route::middleware('admin')->group(function () {

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

        Route::prefix('report')->name('report.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/{transaction}', [ReportController::class, 'show'])->name('show');
        });
    });
});
