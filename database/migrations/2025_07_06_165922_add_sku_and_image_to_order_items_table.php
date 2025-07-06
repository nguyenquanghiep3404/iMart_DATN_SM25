<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkuAndImageToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Thêm cột SKU, đặt tên trực tiếp là 'sku'
            $table->string('sku', 100)->after('product_name');

            // Thêm cột ảnh, đặt tên là 'image_url'
            $table->string('image_url')->nullable()->after('variant_attributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['sku', 'image_url']);
        });
    }
}