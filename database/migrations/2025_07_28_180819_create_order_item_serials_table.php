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
        Schema::create('order_item_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_item_id')
                  ->comment('Liên kết đến dòng sản phẩm trong đơn hàng')
                  ->constrained('order_items')
                  ->onDelete('cascade');

            // Thêm product_variant_id để tiện truy vấn mà không cần join qua order_items
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade');

            // Ghi chú: serial_number ở đây không nên là unique tuyệt đối 
            // vì một sản phẩm có thể bị trả lại và bán cho đơn hàng khác.
            // Tuy nhiên, nó nên là unique cho mỗi order_item_id.
            $table->string('serial_number')->comment('Số IMEI hoặc Serial đã bán');

            $table->enum('status', ['sold', 'returned'])
                  ->default('sold')
                  ->comment('Trạng thái của serial trong đơn hàng');

            $table->timestamps();

            // Đảm bảo một serial chỉ được gán cho một dòng sản phẩm một lần
            $table->unique(['order_item_id', 'serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_serials');
    }
};
