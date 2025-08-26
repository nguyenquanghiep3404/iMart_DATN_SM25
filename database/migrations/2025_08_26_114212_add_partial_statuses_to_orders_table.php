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
        // Thêm 'partially_shipped' và 'partially_delivered' vào enum status
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending_confirmation',
            'processing',
            'in_transit',
            'shipped',
            'out_for_delivery',
            'external_shipping',
            'partially_shipped',
            'delivered',
            'partially_delivered',
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
        // Xóa 'partially_shipped' và 'partially_delivered' khỏi enum status
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
    }
};
