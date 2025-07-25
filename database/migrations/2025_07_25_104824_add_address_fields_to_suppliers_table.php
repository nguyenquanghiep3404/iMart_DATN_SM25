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
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Giả sử bảng của bạn trước đây có một cột 'address' chung chung.
            // Dòng này sẽ xóa cột đó đi để thay thế bằng các cột có cấu trúc hơn.
            // Nếu bạn không có cột 'address' cũ, bạn có thể xóa dòng này.
            if (Schema::hasColumn('suppliers', 'address')) {
                $table->dropColumn('address');
            }

            // Thêm các cột địa chỉ mới, có cấu trúc
            // Đặt các cột mới sau cột 'phone' cho gọn gàng.
            $table->string('address_line')->nullable()->after('phone')->comment('Địa chỉ chi tiết (số nhà, tên đường)');
            $table->string('ward_code')->nullable()->after('address_line')->comment('Mã Phường/Xã');
            $table->string('district_code')->nullable()->after('ward_code')->comment('Mã Quận/Huyện');
            $table->string('province_code')->nullable()->after('district_code')->comment('Mã Tỉnh/Thành phố');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Thao tác đảo ngược: Xóa các cột mới và thêm lại cột 'address' cũ
            $table->dropColumn([
                'address_line',
                'ward_code',
                'district_code',
                'province_code'
            ]);

            // Thêm lại cột 'address' cũ nếu cần
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable();
            }
        });
    }
};
