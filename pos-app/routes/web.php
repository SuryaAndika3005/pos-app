<?php

use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — POS BuildPro
|--------------------------------------------------------------------------
*/

// Arahkan root ke halaman kasir.
Route::redirect('/', '/pos');

// Halaman kasir (katalog produk + keranjang).
Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

// Proses transaksi (AJAX / fetch). Mengembalikan JSON.
Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');

/*
 | Catatan: untuk produksi, bungkus rute di atas dengan middleware 'auth'
 | agar Auth::id() pada controller terisi kasir yang login:
 |
 | Route::middleware('auth')->group(function () { ... });
 */
