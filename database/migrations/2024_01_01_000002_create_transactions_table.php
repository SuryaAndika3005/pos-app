<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel transactions = HEADER nota (1 baris per struk)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();             // mis. INV-20240101-0001
            $table->foreignId('user_id')                            // kasir yang melayani
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Rincian nilai (semua decimal untuk presisi uang)
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);              // mis. PPN 11%
            $table->decimal('total', 15, 2)->default(0);

            // Pembayaran
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'debit'])->default('cash');
            $table->decimal('paid_amount', 15, 2)->default(0);      // uang diterima
            $table->decimal('change_amount', 15, 2)->default(0);    // kembalian

            $table->enum('status', ['pending', 'paid', 'void'])->default('paid');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
