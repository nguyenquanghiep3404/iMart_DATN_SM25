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
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_code')->unique()->comment('Mã lô hàng, dùng để nhận diện duy nhất');
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')
                  ->nullable()
                  ->constrained('purchase_order_items')
                  ->onDelete('set null')
                  ->comment('Liên kết tới mục trên đơn nhập hàng gốc');

            $table->decimal('cost_price', 15, 2)->comment('Giá vốn chính xác của sản phẩm trong lô này');
            $table->date('expiry_date')->nullable()->comment('Ngày hết hạn (nếu có)');
            $table->date('manufacturing_date')->nullable()->comment('Ngày sản xuất (nếu có)');
            $table->unsignedInteger('initial_quantity')->comment('Số lượng ban đầu khi nhập lô');
            $table->unsignedInteger('quantity_on_hand')->comment('Số lượng thực tế còn lại trong lô');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_lots');
    }
};