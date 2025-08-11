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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Thêm cột 'notes' kiểu TEXT, cho phép giá trị NULL, và nằm sau cột 'order_date'
            $table->text('notes')->nullable()->after('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Xóa cột 'notes' nếu rollback migration
            $table->dropColumn('notes');
        });
    }
};