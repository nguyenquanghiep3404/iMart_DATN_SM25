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
        // Bước 1: Thay đổi cột status thành VARCHAR tạm thời
        Schema::table('packages', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
        
        // Bước 2: Mapping dữ liệu packages hiện có từ status cũ sang status mới
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
        
        // Bước 3: Thay đổi cột status thành enum mới
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('status', [
                'pending_confirmation',
                'processing',
                'packed',
                'awaiting_shipment_assigned',
                'out_for_delivery',
                'delivered',
                'cancelled',
                'failed_delivery',
                'returned'
            ])->default('pending_confirmation')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bước 1: Thay đổi cột status thành VARCHAR tạm thời
        Schema::table('packages', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
        
        // Bước 2: Rollback mapping
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
        
        // Bước 3: Khôi phục enum cũ
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'on_hold',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'returned'
            ])->default('pending')->change();
        });
    }
};