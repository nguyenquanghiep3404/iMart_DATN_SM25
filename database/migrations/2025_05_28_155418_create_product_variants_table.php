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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            
            // Nhóm các trường về giá
            $table->decimal('price', 15, 2);
            $table->decimal('cost_price', 15, 2)->default(0)->comment('Giá vốn sản phẩm');
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->timestamp('sale_price_starts_at')->nullable();
            $table->timestamp('sale_price_ends_at')->nullable();
            
            // Nhóm các trường về kho
            $table->boolean('manage_stock')->default(true);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock');
            $table->integer('low_stock_threshold')->unsigned()->default(10)->comment('Ngưỡng cảnh báo tồn kho thấp');
            
            // Nhóm các trường về vận chuyển
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('dimensions_length', 8, 2)->nullable();
            $table->decimal('dimensions_width', 8, 2)->nullable();
            $table->decimal('dimensions_height', 8, 2)->nullable();
            
            // Các trường khác
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Cột điểm thưởng được thêm vào
            $table->unsignedInteger('points_awarded_on_purchase')->default(0)->comment('Số điểm thưởng nhận được khi mua biến thể sản phẩm này');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
