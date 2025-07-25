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
        Schema::create('inventory_lot_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')
                  ->constrained('inventory_lots')
                  ->onDelete('cascade');
            $table->foreignId('store_location_id')
                  ->constrained('store_locations')
                  ->onDelete('cascade');
            $table->unsignedInteger('quantity')->comment('Số lượng của lô này tại địa điểm này');
            $table->timestamps();

            // Đảm bảo không có 2 dòng trùng lặp cho cùng một lô ở cùng một địa điểm
            $table->unique(['lot_id', 'store_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_lot_locations');
    }
};