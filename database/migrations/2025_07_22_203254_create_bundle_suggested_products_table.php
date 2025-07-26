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
        Schema::create('bundle_suggested_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bundle_id')->constrained('product_bundles')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->enum('discount_type', ['fixed_price', 'percentage_discount'])->default('fixed_price')->comment('Loại giảm giá');
            $table->decimal('discount_value', 15, 2)->comment('Giá trị giảm');
            $table->boolean('is_preselected')->default(false)->comment('Sản phẩm có được chọn sẵn không');
            $table->unsignedInteger('display_order')->default(0)->comment('Thứ tự hiển thị');
            $table->unique(['product_bundle_id', 'product_variant_id'], 'bundle_suggested_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_suggested_products');
    }
};
