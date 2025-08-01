<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('status')->default('draft')->comment('Trạng thái chiến dịch: draft, scheduled, sent')->after('email_content');
        });
    }

    public function down()
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

};
