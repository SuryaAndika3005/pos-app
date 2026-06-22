<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->string("sku")->nullable()->unique();
            $table->string("name");
            $table->text("description")->nullable();
            $table->string("category")->nullable()->index();
            $table->string("image")->nullable();
            $table->decimal("price", 15, 2)->default(0);
            $table->decimal("cost_price", 15, 2)->default(0);
            $table->string("unit")->default("pcs");
            $table->enum("unit_type", ["integer", "decimal"])->default("integer");
            $table->decimal("stock", 15, 2)->default(0);
            $table->decimal("min_stock", 15, 2)->default(0);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists("products"); }
};
