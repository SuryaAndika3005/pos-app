<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable()->unique();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();         // catatan internal kasir
            $table->decimal('total_debt', 15, 2)->default(0);   // cache saldo utang aktif
            $table->decimal('total_spent', 15, 2)->default(0);  // cache total belanja all-time
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
