<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên chiến dịch');
            $table->text('description')->nullable()->comment('Mô tả chiến dịch');
            $table->unsignedBigInteger('customer_group_id')->comment('Nhóm khách hàng mục tiêu');
            $table->string('email_subject')->comment('Tiêu đề email');
            $table->text('email_content')->comment('Nội dung email (HTML hoặc text)');
            $table->timestamp('sent_at')->nullable()->comment('Thời điểm gửi');
            $table->timestamps();

            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketing_campaigns');
    }
}
