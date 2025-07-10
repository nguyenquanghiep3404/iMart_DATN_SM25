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
        Schema::create('homepage_product_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề khối
            $table->boolean('is_visible')->default(true); // Bật tắt khối
            $table->unsignedInteger('order')->default(0); // Thứ tự hiển thị
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_product_blocks');
    }
};
