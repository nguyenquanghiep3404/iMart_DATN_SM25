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
        Schema::table('order_fulfillment_items', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('order_fulfillment_id');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->index(['package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillment_items', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropIndex(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
