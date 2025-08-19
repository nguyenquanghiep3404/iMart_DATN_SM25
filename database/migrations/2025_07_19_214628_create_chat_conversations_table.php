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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['support', 'internal'])->default('support')->comment('Loại cuộc hội thoại: hỗ trợ khách hàng hay nội bộ');
            // Đảm bảo bảng 'users' đã tồn tại trước khi chạy migration này
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->comment('ID khách hàng (có thể NULL nếu là chat nội bộ)');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->comment('Admin chịu trách nhiệm chính');
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
