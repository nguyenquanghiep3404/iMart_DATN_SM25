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
        // Cập nhật các trạng thái cũ sang trạng thái mới
        $statusMappings = [
            // Các trạng thái chờ vận chuyển -> đang giao hàng
            'in_transit' => 'out_for_delivery',
            'awaiting_shipment' => 'out_for_delivery',
            'awaiting_shipment_packed' => 'out_for_delivery',
            'awaiting_shipment_assigned' => 'out_for_delivery',
            'shipped' => 'out_for_delivery',
            
            // Trạng thái trả hàng -> hủy
            'returned' => 'cancelled',
        ];

        foreach ($statusMappings as $oldStatus => $newStatus) {
            DB::table('orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại trạng thái cũ (chỉ một số trạng thái có thể khôi phục)
        $reverseStatusMappings = [
            'out_for_delivery' => 'shipped', // Chọn shipped làm trạng thái mặc định
            'cancelled' => 'returned', // Một số đơn hủy có thể là trả hàng
        ];

        foreach ($reverseStatusMappings as $newStatus => $oldStatus) {
            // Chỉ khôi phục một phần, không thể khôi phục chính xác 100%
            // vì nhiều trạng thái cũ được gộp thành một trạng thái mới
        }
    }
};
