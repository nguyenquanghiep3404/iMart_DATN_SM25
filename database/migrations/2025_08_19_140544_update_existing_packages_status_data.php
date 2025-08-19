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
        // Mapping dữ liệu packages hiện có từ status cũ sang status mới
        $statusMapping = [
            'pending' => 'pending_confirmation',
            'on_hold' => 'processing',
            'in_transit' => 'out_for_delivery',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'failed_delivery' => 'failed_delivery',
            'returned' => 'returned'
        ];
        
        foreach ($statusMapping as $oldStatus => $newStatus) {
            \DB::table('packages')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback mapping
        $reverseMapping = [
            'pending_confirmation' => 'pending',
            'processing' => 'on_hold',
            'packed' => 'on_hold',
            'awaiting_shipment_assigned' => 'on_hold',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'pending',
            'failed_delivery' => 'failed_delivery',
            'returned' => 'returned'
        ];
        
        foreach ($reverseMapping as $newStatus => $oldStatus) {
            \DB::table('packages')
                ->where('status', $newStatus)
                ->update(['status' => $oldStatus]);
        }
    }
};
