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
        Schema::create('trade_in_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('store_location_id')->nullable()->constrained('store_locations');
            $table->string('type')->default('used')->index(); // 'used', 'open_box'
            $table->string('sku')->unique();
            $table->string('condition_grade')->nullable();
            $table->text('condition_description');
            $table->decimal('selling_price', 15, 2);
            $table->string('imei_or_serial')->nullable()->unique();
            $table->string('status')->default('available')->index(); // 'available', 'in_cart', 'sold'
            $table->timestamps();
            $table->softDeletes(); // <-- THÊM CỘT XOÁ MỀM TẠI ĐÂY
        });

        Schema::create('trade_in_item_images', function (Blueprint $table) {
            $table->foreignId('trade_in_item_id')->constrained('trade_in_items')->onDelete('cascade');
            $table->foreignId('uploaded_file_id')->constrained('uploaded_files')->onDelete('cascade');
            $table->primary(['trade_in_item_id', 'uploaded_file_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sửa lại: Xóa bảng theo thứ tự ngược lại để đảm bảo khóa ngoại
        Schema::dropIfExists('trade_in_item_images');
        Schema::dropIfExists('trade_in_items');
    }
};
