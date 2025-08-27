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
        // Trước khi xóa các trạng thái, cập nhật các đơn hàng có trạng thái deprecated
        // Chuyển awaiting_shipment, awaiting_shipment_assigned, awaiting_shipment_packed về processing
        DB::table('orders')
            ->whereIn('status', ['awaiting_shipment', 'awaiting_shipment_assigned', 'awaiting_shipment_packed'])
            ->update(['status' => 'processing']);

        // Xóa 3 trạng thái deprecated khỏi enum
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại enum với 3 trạng thái deprecated
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
};
