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
        Schema::table('users', function (Blueprint $table) {
            // Thêm cột 'is_guest' kiểu TINYINT với giá trị mặc định là 0 (false)
            // và đặt nó sau cột 'status'
            $table->tinyInteger('is_guest')->default(0)->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Khi rollback, xóa cột 'is_guest'
            $table->dropColumn('is_guest');
        });
    }
};
