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
        Schema::table('store_locations', function (Blueprint $table) {
            // Đảm bảo dòng này tồn tại và đúng
            $table->enum('type', ['store', 'warehouse', 'service_center'])
                  ->default('store')
                  ->after('name')
                  ->comment('Phân loại địa điểm: cửa hàng, kho, trung tâm bảo hành');

            $table->string('province_code', 20)->nullable()->after('phone');
            $table->string('district_code', 20)->nullable()->after('province_code');
            $table->string('ward_code', 20)->nullable()->after('district_code');

            $table->foreign('province_code')
                  ->references('code')
                  ->on('provinces_old')
                  ->onDelete('set null');

            $table->foreign('district_code')
                  ->references('code')
                  ->on('districts_old')
                  ->onDelete('set null');

            $table->foreign('ward_code')
                  ->references('code')
                  ->on('wards_old')
                  ->onDelete('set null');

            // Đảm bảo dòng này tồn tại và đúng (nếu bạn muốn soft deletes)
            $table->softDeletes();
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
            $table->dropForeign(['province_code']);
            $table->dropForeign(['district_code']);
            $table->dropForeign(['ward_code']);

            // Đảm bảo bạn xóa đúng các cột đã thêm
            $table->dropColumn(['type', 'province_code', 'district_code', 'ward_code']);

            // Đảm bảo dòng này tồn tại và đúng (nếu bạn đã thêm softDeletes trong up)
            $table->dropSoftDeletes();
        });
    }
};
