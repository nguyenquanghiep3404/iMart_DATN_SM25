<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToCustomerGroupsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->softDeletes(); // tạo cột deleted_at
        });
    }

    public function down()
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
