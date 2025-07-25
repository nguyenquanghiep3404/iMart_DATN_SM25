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
        Schema::create('stock_transfers', function (Blueprint $table) {
    $table->id();
    $table->string('transfer_code')->unique();
    $table->foreignId('from_location_id')->constrained('store_locations');
    $table->foreignId('to_location_id')->constrained('store_locations');
    $table->string('status')->default('pending'); // pending, shipped, received
    $table->foreignId('created_by')->constrained('users');
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('received_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
Schema::create('stock_transfer_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained('product_variants');
    $table->integer('quantity');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers_tables');
    }
};
