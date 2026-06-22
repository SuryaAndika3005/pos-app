<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utang pokok — satu baris per transaksi yang menghasilkan utang
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('original_amount', 15, 2);   // nilai utang awal
            $table->decimal('paid_amount', 15, 2)->default(0);   // total yang sudah dibayar
            $table->decimal('remaining_amount', 15, 2);  // sisa (di-update tiap pembayaran)

            $table->enum('status', ['open', 'partial', 'paid'])->default('open')->index();
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();         // tenggat waktu (opsional)

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
        });

        // Tabel riwayat pembayaran utang
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'qris', 'transfer', 'debit'])->default('cash');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
