<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abandoned_cart_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('abandoned_cart_id')
                  ->constrained('abandoned_carts')
                  ->onDelete('cascade')
                  ->comment('Liên kết tới giỏ hàng bị bỏ quên');

            $table->string('action')->comment('Hành động: sent_email, sent_in_app_notification, add_note...');
            $table->text('description')->nullable()->comment('Mô tả chi tiết hoặc nội dung ghi chú');

            $table->nullableMorphs('causer'); // Tạo causer_id, causer_type đều nullable

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_cart_logs');
    }
};
