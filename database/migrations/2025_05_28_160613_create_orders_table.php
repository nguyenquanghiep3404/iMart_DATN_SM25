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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // For registered users
            $table->string('guest_id')->nullable()->index(); // For guest checkouts

            $table->string('order_code')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->string('shipping_country')->default('Vietnam');

            $table->string('shipping_province_code', 20);
            $table->string('shipping_ward_code', 20);

            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->string('billing_country')->nullable();


            $table->string('billing_province_code', 20)->nullable();
            $table->string('billing_ward_code', 20)->nullable();

            $table->decimal('sub_total', 15, 2);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            $table->string('payment_method');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('shipping_method')->nullable();
            $table->enum('status', [
                'pending_confirmation',
                'processing',
                'awaiting_shipment',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled',
                'returned',
                'failed_delivery'
            ])->default('pending_confirmation');

            $table->text('notes_from_customer')->nullable();
            $table->text('notes_for_shipper')->nullable();
            $table->text('admin_note')->nullable();

            $table->date('desired_delivery_date')->nullable();
            $table->string('desired_delivery_time_slot')->nullable();

            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin/Order Processor
            $table->foreignId('shipped_by')->nullable()->constrained('users')->onDelete('set null'); // Shipper User ID

            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('failed_delivery_reason')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1000)->nullable(); // Increased length for user agent

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('shipping_province_code')->references('code')->on('provinces')->onDelete('cascade');
            $table->foreign('shipping_ward_code')->references('code')->on('wards')->onDelete('cascade');
            $table->foreign('billing_province_code')->references('code')->on('provinces')->onDelete('set null');
            $table->foreign('billing_ward_code')->references('code')->on('wards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
