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
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id(); // Primary Key, BigInt, Auto Increment

            // Foreign key liên kết đến bảng product_variants
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade'); // Nếu xóa biến thể, các record kho cũng sẽ bị xóa

            $table->string('inventory_type')->index(); 
            $table->integer('quantity')->default(0); // Số lượng tồn kho

            $table->timestamps(); // created_at và updated_at

            // Tạo một khóa UNIQUE kết hợp để đảm bảo mỗi biến thể
            // chỉ có một hàng cho mỗi loại tồn kho.
            $table->unique(['product_variant_id', 'inventory_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_inventories');
    }
};