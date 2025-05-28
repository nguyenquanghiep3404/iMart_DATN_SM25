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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Polymorphic relationship for commentable items (post, review reply, etc.)
            $table->morphs('commentable'); // Adds commentable_id and commentable_type
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Người bình luận
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // Cho bình luận dạng cây
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
