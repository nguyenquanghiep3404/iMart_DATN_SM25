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
        Schema::create('stocktakes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('store_location_id')->constrained('store_locations');
    $table->string('stocktake_code')->unique();
    $table->string('status')->default('counting'); // counting, completed
    $table->foreignId('started_by')->constrained('users');
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
Schema::create('stocktake_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stocktake_id')->constrained('stocktakes')->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained('product_variants');
    $table->string('inventory_type');
    $table->integer('system_quantity');
    $table->integer('counted_quantity');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktakes_tables');
    }
};
