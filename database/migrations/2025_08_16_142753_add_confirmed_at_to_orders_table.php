<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột mới để lưu thời gian khách hàng xác nhận
            $table->timestamp('confirmed_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');
        });
    }
};
