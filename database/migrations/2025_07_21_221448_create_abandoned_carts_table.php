<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                  ->unique()
                  ->constrained('carts')
                  ->onDelete('cascade')
                  ->comment('Khóa ngoại đến giỏ hàng gốc');

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('ID người dùng nếu là thành viên');

            $table->string('guest_email')->nullable()->comment('Email khách cung cấp (nếu có)');

            $table->enum('status', ['pending', 'recovered', 'archived'])
                  ->default('pending')
                  ->comment('pending: chưa khôi phục, recovered: đã khôi phục, archived: đã lưu trữ');

            $table->enum('email_status', ['unsent', 'sent'])
                  ->default('unsent')
                  ->comment('Trạng thái gửi email');

            $table->enum('in_app_notification_status', ['unsent', 'sent'])
                  ->default('unsent')
                  ->comment('Trạng thái gửi thông báo in-app');

            $table->string('recovery_token', 100)->nullable()->comment('Mã token duy nhất để khôi phục giỏ hàng');

            $table->timestamp('last_notified_at')->nullable()->comment('Lần cuối gửi thông báo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
