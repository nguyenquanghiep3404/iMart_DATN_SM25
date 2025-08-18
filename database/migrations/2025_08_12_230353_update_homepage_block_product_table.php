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
        Schema::table('homepage_block_product', function (Blueprint $table) {
            // Bước 1: Xóa khóa ngoại cũ trước khi xóa cột product_id
            $table->dropForeign(['product_id']);

            // Bước 2: Xóa cột 'product_id'
            $table->dropColumn('product_id');

            // Bước 3: Thêm cột 'product_variant_id' mới
            $table->foreignId('product_variant_id')->after('block_id')->constrained('product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_block_product', function (Blueprint $table) {
            // Bước 1: Xóa khóa ngoại của cột mới
            $table->dropForeign(['product_variant_id']);

            // Bước 2: Xóa cột 'product_variant_id'
            $table->dropColumn('product_variant_id');

            // Bước 3: Thêm lại cột 'product_id' cũ
            $table->foreignId('product_id')->after('block_id')->constrained('products')->onDelete('cascade');
        });
    }
};
