<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'in_transit' status to the enum
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'in_transit',
            'awaiting_shipment',
            'awaiting_shipment_packed',
            'awaiting_shipment_assigned',
            'shipped',
            'out_for_delivery',
            'delivered',
            'cancelled',
            'returned',
            'failed_delivery'
        ) DEFAULT 'pending_confirmation'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'in_transit' status from the enum
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'awaiting_shipment',
            'awaiting_shipment_packed',
            'awaiting_shipment_assigned',
            'shipped',
            'out_for_delivery',
            'delivered',
            'cancelled',
            'returned',
            'failed_delivery'
        ) DEFAULT 'pending_confirmation'");
    }
};
