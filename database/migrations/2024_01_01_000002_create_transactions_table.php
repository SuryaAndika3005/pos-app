<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("transactions", function (Blueprint $table) {
            $table->id();
            $table->string("invoice_number")->unique();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->decimal("subtotal", 15, 2)->default(0);
            $table->decimal("discount", 15, 2)->default(0);
            $table->decimal("tax", 15, 2)->default(0);
            $table->decimal("total", 15, 2)->default(0);
            $table->enum("payment_method", ["cash", "qris", "transfer", "debit"])->default("cash");
            $table->decimal("paid_amount", 15, 2)->default(0);
            $table->decimal("change_amount", 15, 2)->default(0);
            $table->enum("status", ["pending", "paid", "void"])->default("paid");
            $table->text("note")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("transactions"); }
};
