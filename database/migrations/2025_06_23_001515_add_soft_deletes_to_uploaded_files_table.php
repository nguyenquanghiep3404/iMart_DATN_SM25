<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploaded_files', function (Blueprint $table) {
            // Thêm cột deleted_by (user đã xoá)
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Thêm cột deleted_at cho soft delete
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_files', function (Blueprint $table) {
            // Xóa khóa ngoại và cột deleted_by
            $table->dropForeign(['deleted_by']);
            $table->dropColumn('deleted_by');

            // Xóa soft deletes
            $table->dropSoftDeletes();
        });
    }
};
