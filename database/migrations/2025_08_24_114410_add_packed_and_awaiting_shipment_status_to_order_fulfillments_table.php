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
        // Thêm 'packed' và 'awaiting_shipment' vào enum status
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa 'packed' và 'awaiting_shipment' khỏi enum status
        DB::statement("ALTER TABLE order_fulfillments MODIFY status ENUM(
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending'");
    }
};
