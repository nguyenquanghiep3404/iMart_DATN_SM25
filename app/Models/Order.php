<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'guest_id', 'order_code', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address_line1', 'shipping_address_line2', 'shipping_city', 'shipping_district', 'shipping_ward', 'shipping_zip_code', 'shipping_country',
        'billing_address_line1', 'billing_address_line2', 'billing_city', 'billing_district', 'billing_ward', 'billing_zip_code', 'billing_country',
        'sub_total', 'shipping_fee', 'discount_amount', 'tax_amount', 'grand_total',
        'payment_method', 'payment_status', 'shipping_method', 'status',
        'notes_from_customer', 'notes_for_shipper', 'admin_note',
        'processed_by', 'shipped_by', 'delivered_at', 'cancelled_at',
        'cancellation_reason', 'failed_delivery_reason', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}