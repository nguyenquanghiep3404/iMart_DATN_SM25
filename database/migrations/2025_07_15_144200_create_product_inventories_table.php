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
    public function up(): void
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();

            // Foreign key liên kết đến bảng product_variants
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade');

            // THÊM MỚI: Foreign key liên kết đến bảng store_locations
            // Bảng 'store_locations' phải được tạo trước file này
            $table->foreignId('store_location_id')
                  ->constrained('store_locations')
                  ->onDelete('cascade');

            $table->string('inventory_type')->index()->comment("Loại tồn kho: new, defective...");
            $table->integer('quantity')->default(0);

            $table->timestamps();

            // SỬA ĐỔI: Tạo khóa UNIQUE kết hợp cho cả 3 cột ngay từ đầu
            // để đảm bảo mỗi biến thể tại mỗi cửa hàng chỉ có một hàng cho mỗi loại tồn kho.
            $table->unique(['product_variant_id', 'store_location_id', 'inventory_type'], 'inventory_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        
    }
};
