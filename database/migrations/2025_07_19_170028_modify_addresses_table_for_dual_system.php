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
        Schema::table('addresses', function (Blueprint $table) {
            // Xóa các ràng buộc và cột cũ
            $table->dropForeign(['province_code']);
            $table->dropForeign(['ward_code']);
            $table->dropColumn(['province_code', 'ward_code']);

            // Thêm cột xác định hệ thống
            $table->enum('address_system', ['new', 'old'])->nullable()->after('user_id');

            // Thêm các cột cho hệ thống MỚI
            $table->string('new_province_code', 20)->nullable()->after('address_system');
            $table->string('new_ward_code', 20)->nullable()->after('new_province_code');
            $table->foreign('new_province_code')->references('code')->on('provinces_new')->onDelete('set null');
            $table->foreign('new_ward_code')->references('code')->on('wards_new')->onDelete('set null');

            // Thêm các cột cho hệ thống CŨ
            $table->string('old_province_code', 20)->nullable()->after('new_ward_code');
            $table->string('old_district_code', 20)->nullable()->after('old_province_code');
            $table->string('old_ward_code', 20)->nullable()->after('old_district_code');
            $table->foreign('old_province_code')->references('code')->on('provinces_old')->onDelete('set null');
            $table->foreign('old_district_code')->references('code')->on('districts_old')->onDelete('set null');
            $table->foreign('old_ward_code')->references('code')->on('wards_old')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Viết logic down() phức tạp hơn nếu cần, ở đây tạm thời bỏ qua để đơn giản
    }
};
