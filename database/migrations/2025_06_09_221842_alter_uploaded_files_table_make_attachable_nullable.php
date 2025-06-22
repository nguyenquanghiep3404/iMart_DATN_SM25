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
        Schema::table('uploaded_files', function (Blueprint $table) {
            // Cho phép cột attachable_id và attachable_type nhận giá trị NULL
            // change() được dùng để sửa đổi một cột đã tồn tại
            $table->unsignedBigInteger('attachable_id')->nullable()->change();
            $table->string('attachable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploaded_files', function (Blueprint $table) {
            // Logic để hoàn tác lại nếu cần
            $table->unsignedBigInteger('attachable_id')->nullable(false)->change();
            $table->string('attachable_type')->nullable(false)->change();
        });
    }
};