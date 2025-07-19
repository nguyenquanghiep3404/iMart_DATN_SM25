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
        Schema::table('comments', function (Blueprint $table) {
            // Sửa user_id thành nullable
            $table->foreignId('user_id')->nullable()->change();

            // Thêm các trường thông tin cho khách vãng lai
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('guest_phone')->nullable()->after('guest_email');
            
            // Thêm trường giới tính (gender)
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('guest_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Xóa các trường guest nếu có
            $table->dropColumn(['guest_name', 'guest_email', 'guest_phone']);
            
            // Chỉ xóa cột gender nếu nó thực sự tồn tại
            if (Schema::hasColumn('comments', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }

};
