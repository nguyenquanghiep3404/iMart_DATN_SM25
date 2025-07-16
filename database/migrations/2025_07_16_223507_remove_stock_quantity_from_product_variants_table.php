<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa cột stock_quantity.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            if (Schema::hasColumn('product_variants', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Thêm lại cột stock_quantity nếu cần rollback.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('sale_price_ends_at');
            }
        });
    }
};