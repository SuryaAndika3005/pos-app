<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Identitas produk
            $table->string('sku')->nullable()->unique();          // kode barang
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable()->index();       // Busa, Kain, Dakron, dll
            $table->string('image')->nullable();                   // path di storage

            // Harga (pakai decimal, JANGAN float, agar uang presisi)
            $table->decimal('price', 15, 2)->default(0);           // harga jual
            $table->decimal('cost_price', 15, 2)->default(0);      // harga modal / HPP (untuk laba ERP)

            // ── Satuan & Stok ─────────────────────────────────────────────
            // unit      : label satuan yang ditampilkan (mtr, lbr, pcs, kg)
            // unit_type : 'decimal' => boleh pecahan (kain 2.5 mtr)
            //             'integer' => hanya bilangan bulat (busa 1 lembar)
            $table->string('unit')->default('pcs');
            $table->enum('unit_type', ['integer', 'decimal'])->default('integer');

            // Stok pakai decimal supaya muat satuan meteran (mis. sisa 12.75 mtr)
            $table->decimal('stock', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 2)->default(0);       // ambang reorder / prediksi AI
            // ──────────────────────────────────────────────────────────────

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // arsipkan produk tanpa hapus permanen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
