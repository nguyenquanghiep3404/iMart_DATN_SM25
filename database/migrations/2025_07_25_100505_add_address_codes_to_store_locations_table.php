<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Sử dụng Schema::table để sửa đổi bảng 'store_locations' đã tồn tại
        Schema::table('store_locations', function (Blueprint $table) {
            
            // --- THÊM MỚI: Cột 'type' để phân loại địa điểm ---
            $table->enum('type', ['store', 'warehouse', 'service_center'])
                  ->default('store')
                  ->after('name')
                  ->comment('Phân loại địa điểm: cửa hàng, kho, trung tâm bảo hành');

            // Thêm các cột mã địa chỉ sau cột 'phone'
            $table->string('province_code', 20)->nullable()->after('phone');
            $table->string('district_code', 20)->nullable()->after('province_code');
            $table->string('ward_code', 20)->nullable()->after('district_code');
            
            // --- Thêm các khóa ngoại để liên kết với các bảng địa chỉ "old" ---

            // Liên kết province_code với bảng provinces_old
            $table->foreign('province_code')
                  ->references('code')
                  ->on('provinces_old')
                  ->onDelete('set null'); // Nếu tỉnh bị xóa, set null cho cột này

            // Liên kết district_code với bảng districts_old
            $table->foreign('district_code')
                  ->references('code')
                  ->on('districts_old')
                  ->onDelete('set null'); // Nếu quận/huyện bị xóa, set null

            // Liên kết ward_code với bảng wards_old
            $table->foreign('ward_code')
                  ->references('code')
                  ->on('wards_old')
                  ->onDelete('set null'); // Nếu phường/xã bị xóa, set null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('store_locations', function (Blueprint $table) {
            // --- Xóa các khóa ngoại trước khi xóa cột ---
            // Tên khóa ngoại được Laravel tự tạo theo quy ước: table_column_foreign
            $table->dropForeign(['province_code']);
            $table->dropForeign(['district_code']);
            $table->dropForeign(['ward_code']);

            // --- Xóa các cột đã thêm ---
            // Xóa cả cột 'type' mới
            $table->dropColumn(['type', 'province_code', 'district_code', 'ward_code']);
        });
    }
};
