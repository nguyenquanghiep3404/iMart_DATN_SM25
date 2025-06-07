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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('restrict'); // Hoặc set null nếu sản phẩm/biến thể có thể bị xóa
            $table->string('product_name'); // Snapshot of product name at time of order
            $table->json('variant_attributes')->nullable(); // Snapshot of variant attributes
            $table->unsignedInteger('quantity');
            $table->decimal('price', 15, 2); // Giá tại thời điểm mua
            $table->decimal('total_price', 15, 2); // quantity * price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
