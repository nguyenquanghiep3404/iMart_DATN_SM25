<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật tất cả orders có status 'in_transit' thành 'shipped'
        DB::table('orders')
            ->where('status', 'in_transit')
            ->update(['status' => 'shipped']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: chuyển lại 'shipped' thành 'in_transit' (nếu cần)
        // Lưu ý: điều này có thể không chính xác 100% vì không biết đâu là shipped gốc
        // và đâu là được chuyển từ in_transit
    }
};
