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
        // Thêm 'cancellation_requested' vào danh sách ENUM
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_confirmation','processing','awaiting_shipment','shipped','out_for_delivery','delivered','cancelled','returned','failed_delivery','cancellation_requested') NOT NULL DEFAULT 'pending_confirmation'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trở về danh sách ENUM cũ nếu cần rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_confirmation','processing','awaiting_shipment','shipped','out_for_delivery','delivered','cancelled','returned','failed_delivery') NOT NULL DEFAULT 'pending_confirmation'");
    }

};
