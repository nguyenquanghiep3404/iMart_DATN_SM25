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
        // Add new status values to the enum
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'awaiting_shipment',
            'shipped',
            'out_for_delivery',
            'delivered',
            'cancelled',
            'returned',
            'failed_delivery'
        ) DEFAULT 'pending_confirmation'");
    }
};
