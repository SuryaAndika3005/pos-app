<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("stock_movements", function (Blueprint $table) {
            $table->id();
            $table->foreignId("product_id")->constrained("products")->cascadeOnDelete();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->enum("type", ["in", "out", "adjustment"])->index();
            $table->decimal("quantity", 15, 2);
            $table->decimal("stock_before", 15, 2);
            $table->decimal("stock_after", 15, 2);
            $table->string("source")->default("manual");
            $table->foreignId("transaction_id")->nullable()->constrained("transactions")->nullOnDelete();
            $table->text("note")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("stock_movements"); }
};
