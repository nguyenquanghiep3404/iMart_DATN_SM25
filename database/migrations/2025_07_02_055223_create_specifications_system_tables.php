<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng nhóm các thông số
        Schema::create('specification_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ví dụ: "Cấu hình & Bộ nhớ", "Camera & Màn hình"
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Bảng định nghĩa từng thông số
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_group_id')->constrained('specification_groups');
            $table->string('name'); // Ví dụ: "Hệ điều hành", "Chip xử lý (CPU)", "RAM"
            $table->string('type')->default('text'); // 'text', 'textarea', 'boolean' (cho Có/Không), 'select'
            $table->text('description')->nullable(); // Mô tả thêm nếu cần
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Bảng lưu giá trị thông số cho từng biến thể sản phẩm
        Schema::create('product_specification_values', function (Blueprint $table) {
            $table->id();
            // QUAN TRỌNG: Liên kết với biến thể sản phẩm (product_variants)
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained('specifications')->cascadeOnDelete();
            $table->text('value'); // Giá trị thực tế, ví dụ: "iOS 18", "8 GB", "Có"
            $table->timestamps();

            // Đảm bảo một biến thể chỉ có một giá trị cho một thông số
           $table->unique(['product_variant_id', 'specification_id'], 'spec_value_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification_values');
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('specification_groups');
    }
};