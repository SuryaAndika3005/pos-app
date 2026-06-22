<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("products", function (Blueprint $table) {
            $table->decimal("daily_avg_usage", 15, 4)->nullable()->after("min_stock");
            $table->date("predicted_stockout_at")->nullable()->after("daily_avg_usage");
            $table->timestamp("prediction_updated_at")->nullable()->after("predicted_stockout_at");
        });
    }
    public function down(): void {
        Schema::table("products", function (Blueprint $table) {
            $table->dropColumn(["daily_avg_usage", "predicted_stockout_at", "prediction_updated_at"]);
        });
    }
};
