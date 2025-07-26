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
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên nội bộ để quản lý');
            $table->string('display_title')->comment('Tên hiển thị cho khách');
            $table->text('description')->nullable()->comment('Mô tả chi tiết về deal');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái của deal');
            $table->timestamp('start_date')->nullable()->comment('Ngày bắt đầu áp dụng');
            $table->timestamp('end_date')->nullable()->comment('Ngày kết thúc áp dụng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundles');
    }
};
