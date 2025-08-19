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
        Schema::create('package_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('status')->comment('Trạng thái của gói hàng');
            $table->timestamp('timestamp')->useCurrent()->comment('Thời gian thay đổi trạng thái');
            $table->text('notes')->nullable()->comment('Ghi chú về sự thay đổi trạng thái (ví dụ: "Đã giao cho đơn vị vận chuyển")');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người dùng đã cập nhật trạng thái');
            $table->timestamps();
            
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['package_id']);
            $table->index(['status']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_status_history');
    }
};
