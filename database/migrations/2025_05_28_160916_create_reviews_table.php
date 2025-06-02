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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade'); // Đánh giá theo biến thể
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reviewer
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('set null'); // To verify purchase
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
            $table->index(['product_variant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
