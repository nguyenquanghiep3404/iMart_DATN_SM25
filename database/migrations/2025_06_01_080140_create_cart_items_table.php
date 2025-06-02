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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id(); // Primary Key, BigInt, Auto Increment
            $table->foreignId('cart_id')
                  ->constrained('carts') // Khóa ngoại đến bảng carts
                  ->onDelete('cascade'); // Nếu giỏ hàng bị xóa, các item trong đó cũng bị xóa
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants') // Khóa ngoại đến bảng product_variants
                  ->onDelete('cascade'); // Nếu biến thể sản phẩm bị xóa, item này cũng bị xóa (cân nhắc onDelete('set null') nếu muốn giữ lại item với product_variant_id = null và xử lý riêng)

            $table->unsignedInteger('quantity'); // Số lượng sản phẩm
            $table->decimal('price', 15, 2)->nullable(); // Giá tại thời điểm thêm vào giỏ (tùy chọn, để lưu lại giá nếu giá sản phẩm thay đổi)

            $table->timestamps(); // created_at (thời điểm thêm vào giỏ) and updated_at

            // Ràng buộc duy nhất: một biến thể sản phẩm chỉ xuất hiện một lần trong một giỏ hàng
            // Số lượng sẽ được cập nhật trên bản ghi hiện có.
            $table->unique(['cart_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
