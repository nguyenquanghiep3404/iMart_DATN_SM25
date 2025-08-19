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
        Schema::table('bundle_suggested_products', function (Blueprint $table) {
            // Xoá 2 cột giảm giá
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bundle_suggested_products', function (Blueprint $table) {
            $table->enum('discount_type', ['fixed_price', 'percentage_discount'])
                ->default('fixed_price')
                ->comment('Loại giảm giá');

            $table->decimal('discount_value', 15, 2)
                ->comment('Giá trị giảm');
        });
    }
};
