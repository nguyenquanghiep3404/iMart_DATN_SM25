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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột xác định hệ thống cho shipping
            $table->enum('shipping_address_system', ['new', 'old'])->nullable()->after('shipping_country');

            // Thêm các cột cho hệ thống MỚI - shipping
            $table->string('shipping_new_province_code', 20)->nullable()->after('shipping_address_system');
            $table->string('shipping_new_ward_code', 20)->nullable()->after('shipping_new_province_code');
            $table->foreign('shipping_new_province_code')->references('code')->on('provinces_new')->onDelete('set null');
            $table->foreign('shipping_new_ward_code')->references('code')->on('wards_new')->onDelete('set null');

            // Thêm các cột cho hệ thống CŨ - shipping
            $table->string('shipping_old_province_code', 20)->nullable()->after('shipping_new_ward_code');
            $table->string('shipping_old_district_code', 20)->nullable()->after('shipping_old_province_code');
            $table->string('shipping_old_ward_code', 20)->nullable()->after('shipping_old_district_code');
            $table->foreign('shipping_old_province_code')->references('code')->on('provinces_old')->onDelete('set null');
            $table->foreign('shipping_old_district_code')->references('code')->on('districts_old')->onDelete('set null');
            $table->foreign('shipping_old_ward_code')->references('code')->on('wards_old')->onDelete('set null');

            // Thêm cột xác định hệ thống cho billing
            $table->enum('billing_address_system', ['new', 'old'])->nullable()->after('billing_country');

            // Thêm các cột cho hệ thống MỚI - billing
            $table->string('billing_new_province_code', 20)->nullable()->after('billing_address_system');
            $table->string('billing_new_ward_code', 20)->nullable()->after('billing_new_province_code');
            $table->foreign('billing_new_province_code')->references('code')->on('provinces_new')->onDelete('set null');
            $table->foreign('billing_new_ward_code')->references('code')->on('wards_new')->onDelete('set null');

            // Thêm các cột cho hệ thống CŨ - billing
            $table->string('billing_old_province_code', 20)->nullable()->after('billing_new_ward_code');
            $table->string('billing_old_district_code', 20)->nullable()->after('billing_old_province_code');
            $table->string('billing_old_ward_code', 20)->nullable()->after('billing_old_district_code');
            $table->foreign('billing_old_province_code')->references('code')->on('provinces_old')->onDelete('set null');
            $table->foreign('billing_old_district_code')->references('code')->on('districts_old')->onDelete('set null');
            $table->foreign('billing_old_ward_code')->references('code')->on('wards_old')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Viết logic down() phức tạp hơn nếu cần, ở đây tạm thời bỏ qua để đơn giản
    }
};