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
        Schema::table('order_fulfillments', function (Blueprint $table) {
            // Thêm trường shipper_id để gán shipper cho từng fulfillment
            $table->foreignId('shipper_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->after('store_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropForeign(['shipper_id']);
            $table->dropColumn('shipper_id');
        });
    }
};
