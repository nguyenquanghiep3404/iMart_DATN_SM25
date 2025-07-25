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
       Schema::create('inventory_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained('product_variants');
    $table->foreignId('store_location_id')->constrained('store_locations');
    $table->string('inventory_type');
    $table->integer('quantity_change');
    $table->integer('quantity_after_change');
    $table->string('reason');
    $table->morphs('reference');
    $table->foreignId('user_id')->nullable()->constrained('users');
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
