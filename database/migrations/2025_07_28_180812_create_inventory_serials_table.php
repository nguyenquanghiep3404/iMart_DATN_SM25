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
        Schema::create('inventory_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade');

            $table->foreignId('lot_id')
                  ->comment('Lô hàng đã nhập chứa serial này')
                  ->constrained('inventory_lots')
                  ->onDelete('cascade');
            
            $table->foreignId('store_location_id')
                  ->comment('Vị trí hiện tại của sản phẩm (kho/cửa hàng)')
                  ->constrained('store_locations')
                  ->onDelete('cascade');

            $table->string('serial_number')->unique()->comment('Số IMEI hoặc Serial Number duy nhất');

            $table->enum('status', ['available', 'transferred', 'sold', 'defective', 'returned'])
                  ->default('available')
                  ->comment('Trạng thái của serial trong kho');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_serials');
    }
};
