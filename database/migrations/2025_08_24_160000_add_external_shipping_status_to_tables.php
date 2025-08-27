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
        // Thêm 'external_shipping' vào enum status của bảng orders
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'in_transit',
            'shipped',
            'out_for_delivery',
            'external_shipping',
            'delivered',
            'cancelled',
            'returned',
            'failed_delivery'
        ) DEFAULT 'pending_confirmation'");

        // Thêm 'external_shipping' vào enum status của bảng order_fulfillments
        DB::statement("ALTER TABLE order_fulfillments MODIFY status ENUM(
            'pending',
            'processing',
            'packed',
            'awaiting_shipment',
            'shipped',
            'external_shipping',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa 'external_shipping' khỏi enum status của bảng orders
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'in_transit',
            'shipped',
            'out_for_delivery',
            'delivered',
            'cancelled',
            'returned',
            'failed_delivery'
        ) DEFAULT 'pending_confirmation'");

        // Xóa 'external_shipping' khỏi enum status của bảng order_fulfillments
        DB::statement("ALTER TABLE order_fulfillments MODIFY status ENUM(
            'pending',
            'processing',
            'packed',
            'awaiting_shipment',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending'");
    }
};