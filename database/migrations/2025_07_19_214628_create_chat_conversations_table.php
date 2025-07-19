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
            $table->enum('type', ['support', 'internal'])->default('support');
            $table->foreignId('user_id')->nullable()->comment('ID khách hàng')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->comment('Admin chịu trách nhiệm')->constrained('users')->onDelete('set null');
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
        // Xóa bảng khi rollback
        Schema::dropIfExists('chat_conversations');
    }
};
