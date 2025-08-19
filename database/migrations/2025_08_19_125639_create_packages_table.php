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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_fulfillment_id')->comment('Liên kết đến một bản ghi thực hiện đơn hàng');
            $table->string('package_code')->unique()->comment('Mã gói hàng duy nhất');
            $table->string('description')->nullable()->comment('Mô tả gói hàng (ví dụ: "Sản phẩm điện thoại và phụ kiện")');
            $table->string('shipping_carrier')->nullable()->comment('Đơn vị vận chuyển');
            $table->string('tracking_code')->nullable()->comment('Mã theo dõi của gói hàng');
            $table->enum('status', [
                'pending',
                'on_hold', 
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'returned'
            ])->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->foreign('order_fulfillment_id')->references('id')->on('order_fulfillments')->onDelete('cascade');
            $table->index(['order_fulfillment_id']);
            $table->index(['status']);
            $table->index(['tracking_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
