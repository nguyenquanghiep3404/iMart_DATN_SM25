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
    Schema::create('cancellation_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->comment('Liên kết đến đơn hàng cần hủy');
        $table->foreignId('user_id')->constrained('users')->comment('Người dùng yêu cầu hủy');
        $table->string('cancellation_code')->unique()->comment('Mã yêu cầu hủy duy nhất');
        $table->text('reason')->nullable()->comment('Lý do hủy khách hàng chọn');
        $table->string('status')->default('pending_review')->comment('pending_review, approved, rejected');
        $table->text('rejection_reason')->nullable()->comment('Lý do admin từ chối (nếu có)');
        $table->string('refund_method')->nullable()->comment('Phương thức hoàn tiền khách chọn');
        $table->decimal('refund_amount', 15, 2)->nullable()->comment('Số tiền cần hoàn');
        $table->string('bank_name')->nullable();
        $table->string('bank_account_name')->nullable();
        $table->string('bank_account_number')->nullable();
        $table->text('admin_note')->nullable()->comment('Ghi chú nội bộ của admin');
        $table->foreignId('approved_by')->nullable()->constrained('users')->comment('Admin đã duyệt yêu cầu');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
