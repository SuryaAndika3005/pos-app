<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'cashier' hanya bisa akses Kasir POS.
            // 'admin' bisa akses semua (Kasir, Gudang, Laporan).
            $table->enum('role', ['cashier', 'admin'])->default('cashier')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
