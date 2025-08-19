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
        // Thêm 'packed' vào ENUM status của bảng order_fulfillments
        DB::statement("ALTER TABLE order_fulfillments MODIFY COLUMN status ENUM(
            'pending',
            'processing', 
            'packed',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trước khi xóa 'packed', cập nhật các bản ghi có status 'packed' thành 'processing'
        DB::table('order_fulfillments')
            ->where('status', 'packed')
            ->update(['status' => 'processing']);
            
        // Khôi phục ENUM status ban đầu
        DB::statement("ALTER TABLE order_fulfillments MODIFY COLUMN status ENUM(
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending'");
    }
};