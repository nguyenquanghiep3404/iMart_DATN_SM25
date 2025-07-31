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
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            // Đảm bảo bảng 'chat_conversations' đã tồn tại trước khi chạy migration này
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            // Đảm bảo bảng 'users' đã tồn tại trước khi chạy migration này
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            // Đảm bảo sự kết hợp conversation_id và user_id là duy nhất
            $table->unique(['conversation_id', 'user_id'], 'conversation_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};
