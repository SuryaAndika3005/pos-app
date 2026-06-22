<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Nama pelanggan untuk identifikasi antrean
            $table->string('customer_name', 100)->nullable()->after('user_id');

            // Status antrean: 'paid' (sudah bayar, menunggu barang) | 'completed' (barang sudah diserahkan)
            // Nilai lama 'paid' di-repurpose: transaksi masih aktif di antrean hingga dikomplitkan.
            // Tambahkan 'completed' sebagai status akhir.
            $table->string('queue_status', 20)->default('active')->after('status');
            // Nilai: 'waiting' = menunggu pembayaran, 'paid' = sudah bayar menunggu barang, 'completed' = selesai
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'queue_status']);
        });
    }
};
