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
        // Kiểm tra xem cột product_variant_id có tồn tại không
        $columns = Schema::getColumnListing('cart_items');
        
        if (in_array('product_variant_id', $columns)) {
            // Kiểm tra và xóa foreign key an toàn
            $foreignKeys = $this->getForeignKeys('cart_items');
            if (in_array('cart_items_product_variant_id_foreign', $foreignKeys)) {
                Schema::table('cart_items', function (Blueprint $table) {
                    $table->dropForeign('cart_items_product_variant_id_foreign');
                });
            }

            Schema::table('cart_items', function (Blueprint $table) {
                // Xóa cột cũ
                $table->dropColumn('product_variant_id');
            });
        }

        // Kiểm tra xem các cột polymorphic đã tồn tại chưa
        if (!in_array('cartable_id', $columns)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unsignedBigInteger('cartable_id');
            });
        }
        
        if (!in_array('cartable_type', $columns)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('cartable_type');
            });
        }

        // Xóa unique constraint cũ nếu tồn tại
        try {
            DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_cart_id_product_variant_id_unique');
        } catch (Exception $e) {
            // Constraint không tồn tại, bỏ qua
        }

        // Thêm unique constraint mới nếu chưa tồn tại
        try {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unique(['cart_id', 'cartable_id', 'cartable_type'], 'cart_items_unique_polymorphic');
            });
        } catch (Exception $e) {
            // Constraint đã tồn tại, bỏ qua
        }
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
        Schema::table('cart_items', function (Blueprint $table) {
            // Xóa các cột đa hình
            $table->dropUnique('cart_items_unique_polymorphic');
            $table->dropColumn(['cartable_id', 'cartable_type']);
            
            // Thêm lại cột cũ
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->unique(['cart_id', 'product_variant_id']);
        });
    }
};
