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
            $table->string('channel')->default('email')->after('customer_group_id');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('channel');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
            $table->dropColumn('channel');
            $table->dropSoftDeletes();
        });
    }
};
