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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            // Đảm bảo bảng 'chat_conversations' đã tồn tại trước khi chạy migration này
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            // Đảm bảo bảng 'users' đã tồn tại trước khi chạy migration này
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade')->comment('ID người gửi (luôn là một user_id)');
            $table->text('content');
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
