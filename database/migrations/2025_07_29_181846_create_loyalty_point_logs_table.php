<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loyalty_point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null')->comment('Đơn hàng mà khách được cộng điểm');
            $table->integer('points')->comment('Số điểm thay đổi (dương là cộng, âm là trừ)');
            $table->enum('type', ['earn', 'spend', 'refund', 'manual_adjustment', 'expire'])->comment('Loại giao dịch');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loyalty_point_logs');
    }
};
