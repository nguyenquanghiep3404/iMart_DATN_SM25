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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount'])->default('fixed_amount');
            $table->decimal('value', 15, 2); // Giá trị giảm
            $table->unsignedInteger('max_uses')->nullable(); // Tổng số lần sử dụng tối đa
            $table->unsignedInteger('max_uses_per_user')->nullable(); // Số lần sử dụng tối đa cho mỗi người dùng
            $table->decimal('min_order_amount', 15, 2)->nullable(); // Giá trị đơn hàng tối thiểu để áp dụng
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
