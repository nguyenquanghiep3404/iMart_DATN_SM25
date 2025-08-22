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
        // Cập nhật enum status của bảng packages theo luồng nghiệp vụ mới
        DB::statement("ALTER TABLE packages MODIFY COLUMN status ENUM(
            'pending_confirmation',
            'processing', 
            'packed',
            'awaiting_shipment_assigned',
            'out_for_delivery',
            'delivered',
            'cancelled',
            'failed_delivery',
            'returned'
        ) DEFAULT 'pending_confirmation'");
        
        // Cập nhật các trạng thái cũ sang trạng thái mới
        DB::table('packages')
            ->where('status', 'pending')
            ->update(['status' => 'pending_confirmation']);
            
        DB::table('packages')
            ->where('status', 'on_hold')
            ->update(['status' => 'processing']);
            
        DB::table('packages')
            ->where('status', 'in_transit')
            ->update(['status' => 'out_for_delivery']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại enum status cũ
        DB::statement("ALTER TABLE packages MODIFY COLUMN status ENUM(
            'pending',
            'on_hold', 
            'in_transit',
            'out_for_delivery',
            'delivered',
            'failed_delivery',
            'returned'
        ) DEFAULT 'pending'");
        
        // Khôi phục lại các trạng thái cũ
        DB::table('packages')
            ->where('status', 'pending_confirmation')
            ->update(['status' => 'pending']);
            
        DB::table('packages')
            ->where('status', 'processing')
            ->update(['status' => 'on_hold']);
            
        DB::table('packages')
            ->where('status', 'packed')
            ->update(['status' => 'pending']);
            
        DB::table('packages')
            ->where('status', 'awaiting_shipment_assigned')
            ->update(['status' => 'pending']);
            
        DB::table('packages')
            ->where('status', 'cancelled')
            ->update(['status' => 'pending']);
    }
};