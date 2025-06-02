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
        Schema::create('carts', function (Blueprint $table) {
            $table->id(); // Primary Key, BigInt, Auto Increment
            $table->foreignId('user_id')
                  ->unique() // Mỗi user chỉ có một giỏ hàng chính
                  ->constrained('users') // Khóa ngoại đến bảng users
                  ->onDelete('cascade'); // Nếu user bị xóa, giỏ hàng của họ cũng bị xóa
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
