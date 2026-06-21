<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom ini sengaja KOSONG/NULL secara default.
     * Diisi oleh proses eksternal (mis. job/command yang menjalankan model
     * prediksi Python) — bukan oleh aplikasi POS ini. Halaman Gudang hanya
     * MENAMPILKAN nilainya jika ada; tidak menghitungnya sendiri.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Rata-rata pemakaian/penjualan per hari (hasil hitung model).
            $table->decimal('daily_avg_usage', 15, 4)->nullable()->after('min_stock');

            // Perkiraan tanggal stok akan habis berdasarkan tren penjualan.
            $table->date('predicted_stockout_at')->nullable()->after('daily_avg_usage');

            // Kapan prediksi terakhir di-update (agar tahu data basi atau belum pernah dihitung).
            $table->timestamp('prediction_updated_at')->nullable()->after('predicted_stockout_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['daily_avg_usage', 'predicted_stockout_at', 'prediction_updated_at']);
        });
    }
};
