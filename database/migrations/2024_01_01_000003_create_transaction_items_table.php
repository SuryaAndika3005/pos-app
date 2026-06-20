<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel transaction_items = DETAIL nota (banyak baris per transaksi).
        // Di sinilah kuantitas DESIMAL berperan (mis. 2.5 meter kain).
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();

            // Snapshot data saat transaksi terjadi (agar nota lama tidak berubah
            // walau master produk nanti diedit/dihapus).
            $table->string('product_name');
            $table->string('unit')->default('pcs');

            $table->decimal('price', 15, 2);        // harga satuan saat transaksi
            $table->decimal('quantity', 15, 2);     // DESIMAL: muat 2.5 mtr / 1 lbr
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);     // (price * quantity) - discount

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
