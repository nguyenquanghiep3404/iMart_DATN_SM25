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
        Schema::table('provinces_old', function (Blueprint $table) {
            // Thêm cột region sau cột name_with_type
            $table->enum('region', ['north', 'central', 'south'])
                  ->nullable() // Cho phép null ban đầu
                  ->after('name_with_type')
                  ->comment('Phân loại vùng miền: Bắc, Trung, Nam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provinces_old', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};