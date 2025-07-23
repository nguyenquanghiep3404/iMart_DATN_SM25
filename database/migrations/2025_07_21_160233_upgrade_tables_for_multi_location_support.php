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
       Schema::table('orders', function (Blueprint $table) {
    $table->string('channel')->default('web')->after('id');
    $table->foreignId('store_location_id')->nullable()->after('user_id')->constrained('store_locations')->onDelete('set null');
    $table->foreignId('register_id')->nullable()->after('store_location_id')->constrained('registers')->onDelete('set null');
    $table->foreignId('pos_session_id')->nullable()->after('register_id')->constrained('pos_sessions')->onDelete('set null');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
