<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Lệnh này sẽ tự động thêm cột `deleted_at` kiểu timestamp và cho phép NULL
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Lệnh này sẽ xóa cột `deleted_at` nếu bạn rollback migration
            $table->dropSoftDeletes();
        });
    }
};
