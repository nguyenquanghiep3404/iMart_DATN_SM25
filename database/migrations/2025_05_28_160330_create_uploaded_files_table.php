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
    Schema::create('uploaded_files', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('attachable_id')->index();
    $table->string('attachable_type')->index();
    $table->string('path'); // Đường dẫn tương đối từ disk root
    $table->string('filename'); // Tên file đã được hash/unique
    $table->string('original_name')->nullable(); // Tên file gốc
    $table->string('mime_type')->nullable();
    $table->unsignedInteger('size')->nullable(); // Kích thước file (bytes)
    $table->string('disk')->default('public');
    $table->string('type')->nullable()->index(); // 'cover_image', 'gallery_image', 'avatar', 'variant_image', 'category_image' etc.
    $table->integer('order')->nullable()->default(0);
    $table->string('alt_text')->nullable();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Người upload
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
