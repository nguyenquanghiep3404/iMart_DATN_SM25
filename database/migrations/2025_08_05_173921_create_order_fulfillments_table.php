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
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();

            // Liên kết với đơn hàng gốc
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');

            // Địa điểm (kho/cửa hàng) chịu trách nhiệm xử lý lượt này
            $table->foreignId('store_location_id')
                  ->constrained('store_locations')
                  ->onDelete('restrict');

            // Mã vận đơn cho gói hàng này (nếu có)
            $table->string('tracking_code')->nullable();
            
            // Đơn vị vận chuyển cho gói hàng này
            $table->string('shipping_carrier')->nullable();

            // Trạng thái của lượt xử lý/gói hàng
            $table->enum('status', [
                'pending',          // Mới tạo, chờ xử lý
                'processing',       // Đang lấy hàng, đóng gói
                'shipped',          // Đã bàn giao cho đơn vị vận chuyển
                'delivered',        // Đã giao thành công
                'cancelled',        // Đã hủy
            ])->default('pending');

            // Các mốc thời gian quan trọng
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillments');
    }
};
