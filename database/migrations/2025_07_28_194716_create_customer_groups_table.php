<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Tên nhóm: Khách hàng mới, Thân thiết, VIP...');
            $table->string('slug')->unique();
            $table->text('description')->nullable()->comment('Mô tả nhóm');
            $table->unsignedInteger('min_order_count')->default(0)->comment('Số đơn hàng tối thiểu');
            $table->decimal('min_total_spent', 15, 2)->default(0.00)->comment('Tổng chi tiêu tối thiểu');
            $table->unsignedInteger('priority')->default(0)->comment('Độ ưu tiên để xác định nhóm khi khách hàng thoả nhiều điều kiện');
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};
