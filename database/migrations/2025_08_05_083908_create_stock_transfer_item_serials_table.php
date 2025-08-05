<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfer_item_serials', function (Blueprint $table) {
            // CÁC CỘT DỮ LIỆU
            $table->id(); // Tương đương: bigIncrements('id') -> bigint, unsigned, auto-increment, primary key

            $table->unsignedBigInteger('stock_transfer_item_id')->comment('Liên kết đến dòng sản phẩm trong phiếu chuyển');
            $table->unsignedBigInteger('inventory_serial_id')->comment('Liên kết đến sản phẩm cụ thể theo serial');

            $table->enum('status', ['in_transit', 'received', 'missing'])
                  ->default('in_transit')
                  ->comment('Trạng thái của serial trong quá trình vận chuyển');

            $table->timestamps(); // Tự động tạo 2 cột: created_at và updated_at (kiểu timestamp, nullable)

            // CÁC RÀNG BUỘC VÀ CHỈ MỤC (CONSTRAINTS & INDEXES)

            // 1. Khóa ngoại đến bảng stock_transfer_items
            $table->foreign('stock_transfer_item_id')
                  ->references('id')
                  ->on('stock_transfer_items')
                  ->onDelete('cascade'); // Nếu dòng item bị xóa, các serial liên quan cũng bị xóa

            // 2. Khóa ngoại đến bảng inventory_serials
            $table->foreign('inventory_serial_id')
                  ->references('id')
                  ->on('inventory_serials')
                  ->onDelete('cascade'); // Nếu serial gốc bị xóa, bản ghi ở đây cũng bị xóa

            // 3. Ràng buộc UNIQUE để tránh trùng lặp dữ liệu
            $table->unique(
                ['stock_transfer_item_id', 'inventory_serial_id'],
                'transfer_item_serial_unique' // Tên của chỉ mục unique
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_transfer_item_serials');
    }
};