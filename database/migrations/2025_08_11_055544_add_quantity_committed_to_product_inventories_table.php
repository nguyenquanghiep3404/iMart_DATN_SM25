<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->integer('quantity_committed')->default(0)->after('quantity')
                  ->comment('Số lượng đã tạm giữ cho các đơn hàng chưa giao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropColumn('quantity_committed');
        });
    }
};
