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
        // Xóa foreign key constraints trước
        $foreignKeys = $this->getForeignKeys('orders');
        
        if (in_array('orders_shipping_province_code_foreign', $foreignKeys)) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign('orders_shipping_province_code_foreign');
            });
        }
        
        if (in_array('orders_shipping_ward_code_foreign', $foreignKeys)) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign('orders_shipping_ward_code_foreign');
            });
        }
        
        if (in_array('orders_billing_province_code_foreign', $foreignKeys)) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign('orders_billing_province_code_foreign');
            });
        }
        
        if (in_array('orders_billing_ward_code_foreign', $foreignKeys)) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign('orders_billing_ward_code_foreign');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            // Xóa các cột cũ
            $table->dropColumn([
                'shipping_province_code',
                'shipping_ward_code',
                'billing_province_code',
                'billing_ward_code'
            ]);
        });
    }

    /**
     * Get foreign keys for a table
     */
    private function getForeignKeys($tableName)
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$tableName]);
        
        return array_column($foreignKeys, 'CONSTRAINT_NAME');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm lại các cột cũ
            $table->string('shipping_province_code', 20)->nullable()->after('shipping_country');
            $table->string('shipping_ward_code', 20)->nullable()->after('shipping_province_code');
            $table->string('billing_province_code', 20)->nullable()->after('billing_country');
            $table->string('billing_ward_code', 20)->nullable()->after('billing_province_code');
        });
    }
};
