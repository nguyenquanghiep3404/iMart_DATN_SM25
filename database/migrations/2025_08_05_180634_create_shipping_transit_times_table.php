<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_shipping_transit_times_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_transit_times', function (Blueprint $table) {
            $table->id();
            $table->string('carrier_name')->index();
            $table->string('from_province_code', 20);
            $table->string('to_province_code', 20);
            $table->unsignedTinyInteger('transit_days_min')->default(1);
            $table->unsignedTinyInteger('transit_days_max')->default(1);
            $table->unique(['carrier_name', 'from_province_code', 'to_province_code'], 'shipping_route_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_transit_times');
    }
};