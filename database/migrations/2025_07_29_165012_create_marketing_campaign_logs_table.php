<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingCampaignLogsTable extends Migration
{
    public function up()
    {
        Schema::create('marketing_campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_campaign_id')->comment('ID chiến dịch marketing');
            $table->unsignedBigInteger('user_id')->comment('ID khách hàng');
            $table->timestamp('sent_at')->nullable()->comment('Thời điểm gửi mail');
            $table->string('status')->default('pending')->comment('Trạng thái gửi: pending, sent, failed');
            $table->text('error_message')->nullable()->comment('Lỗi khi gửi mail nếu có');
            $table->timestamps();

            $table->foreign('marketing_campaign_id')->references('id')->on('marketing_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['marketing_campaign_id', 'user_id'], 'unique_campaign_user');
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketing_campaign_logs');
    }
}
