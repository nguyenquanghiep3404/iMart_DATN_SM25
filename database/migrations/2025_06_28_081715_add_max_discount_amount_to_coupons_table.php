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
        Schema::table('coupons', function (Blueprint $table) {
            // Thêm trường số tiền giảm tối đa (chỉ áp dụng cho mã giảm theo %)
            $table->decimal('max_discount_amount', 12, 2)->nullable()->after('value')
                  ->comment('Số tiền giảm tối đa khi sử dụng mã giảm theo phần trăm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Xóa trường max_discount_amount khi rollback
            $table->dropColumn('max_discount_amount');
        });
    }
};
