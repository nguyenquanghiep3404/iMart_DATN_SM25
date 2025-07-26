<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('service_location_id')->nullable()
                ->comment('ID của trung tâm bảo hành xử lý yêu cầu')
                ->constrained('store_locations')
                ->onDelete('set null');
            $table->foreignId('order_item_id')->constrained('order_items');
            $table->string('claim_code')->unique();
            $table->text('reported_defect');
            $table->timestamp('item_received_at')->nullable();
            $table->string('status')->default('pending_review');
            $table->string('resolution')->nullable();
            $table->text('technician_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};