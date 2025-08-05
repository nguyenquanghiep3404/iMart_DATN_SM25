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
        Schema::create('order_fulfillment_items', function (Blueprint $table) {
            $table->id();

            // Liên kết với lượt xử lý/gói hàng
            $table->foreignId('order_fulfillment_id')
                  ->constrained('order_fulfillments')
                  ->onDelete('cascade');

            // Liên kết với dòng sản phẩm trong đơn hàng gốc
            $table->foreignId('order_item_id')
                  ->constrained('order_items')
                  ->onDelete('cascade');

            // Số lượng của sản phẩm này trong gói hàng này
            // (Quan trọng cho trường hợp 1 item được chia ra gửi từ nhiều nơi)
            $table->unsignedInteger('quantity');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillment_items');
    }
};