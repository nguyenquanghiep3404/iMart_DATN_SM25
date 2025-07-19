<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_AWAITING_SHIPMENT = 'awaiting_shipment';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_FAILED_DELIVERY = 'failed_delivery';

    // Payment status constants
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $fillable = [
        'user_id', 'guest_id', 'order_code', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address_line1', 'shipping_address_line2', 'shipping_zip_code', 'shipping_country',
        'shipping_address_system', 'shipping_new_province_code', 'shipping_new_ward_code',
        'shipping_old_province_code', 'shipping_old_district_code', 'shipping_old_ward_code',
        'billing_address_line1', 'billing_address_line2', 'billing_zip_code', 'billing_country',
        'billing_address_system', 'billing_new_province_code', 'billing_new_ward_code',
        'billing_old_province_code', 'billing_old_district_code', 'billing_old_ward_code',
        'sub_total', 'shipping_fee', 'discount_amount', 'tax_amount', 'grand_total',
        'payment_method', 'payment_status', 'shipping_method', 'status',
        'notes_from_customer', 'notes_for_shipper', 'admin_note',
        'desired_delivery_date', 'desired_delivery_time_slot',
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
        'cancelled_at' => 'datetime'
    ];

    // Helper methods
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING_CONFIRMATION => 'Chờ xác nhận',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_AWAITING_SHIPMENT => 'Chờ giao hàng',
            self::STATUS_SHIPPED => 'Đã xuất kho',
            self::STATUS_OUT_FOR_DELIVERY => 'Đang giao hàng',
            self::STATUS_DELIVERED => 'Giao thành công',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RETURNED => 'Đã trả hàng',
            self::STATUS_FAILED_DELIVERY => 'Giao hàng thất bại'
        ];
    }

    public function getStatusTextAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? 'N/A';
    }

    public function isEditable()
    {
        return !in_array($this->status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RETURNED
        ]);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_PENDING_CONFIRMATION,
            self::STATUS_PROCESSING,
            self::STATUS_AWAITING_SHIPMENT,
            self::STATUS_SHIPPED,
            self::STATUS_OUT_FOR_DELIVERY
        ]);
    }

    // Relationships
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

    // Địa chỉ mới - Shipping
    public function shippingNewProvince()
    {
        return $this->belongsTo(Province::class, 'shipping_new_province_code', 'code');
    }

    public function shippingNewWard()
    {
        return $this->belongsTo(Ward::class, 'shipping_new_ward_code', 'code');
    }

    // Hệ thống CŨ - Shipping
    public function shippingOldProvince()
    {
        return $this->belongsTo(ProvinceOld::class, 'shipping_old_province_code', 'code');
    }

    public function shippingOldDistrict()
    {
        return $this->belongsTo(DistrictOld::class, 'shipping_old_district_code', 'code');
    }

    public function shippingOldWard()
    {
        return $this->belongsTo(WardOld::class, 'shipping_old_ward_code', 'code');
    }

    // Hệ thống MỚI - Billing
    public function billingNewProvince()
    {
        return $this->belongsTo(Province::class, 'billing_new_province_code', 'code');
    }

    public function billingNewWard()
    {
        return $this->belongsTo(Ward::class, 'billing_new_ward_code', 'code');
    }

    // Địa chỉ cũ - Billing
    public function billingOldProvince()
    {
        return $this->belongsTo(ProvinceOld::class, 'billing_old_province_code', 'code');
    }

    public function billingOldDistrict()
    {
        return $this->belongsTo(DistrictOld::class, 'billing_old_district_code', 'code');
    }

    public function billingOldWard()
    {
        return $this->belongsTo(WardOld::class, 'billing_old_ward_code', 'code');
    }

    // Quan hệ động dựa trên địa chỉ mới - Shipping
    public function shippingProvince()
    {
        if ($this->shipping_address_system === 'new') {
            return $this->shippingNewProvince();
        } else {
            return $this->shippingOldProvince();
        }
    }

    public function shippingWard()
    {
        if ($this->shipping_address_system === 'new') {
            return $this->shippingNewWard();
        } else {
            return $this->shippingOldWard();
        }
    }

    public function shippingDistrict()
    {
        if ($this->shipping_address_system === 'old') {
            return $this->shippingOldDistrict();
        }
        return null;
    }

    // Quan hệ động dựa trên địa chỉ mới - Billing
    public function billingProvince()
    {
        if ($this->billing_address_system === 'new') {
            return $this->billingNewProvince();
        } else {
            return $this->billingOldProvince();
        }
    }

    public function billingWard()
    {
        if ($this->billing_address_system === 'new') {
            return $this->billingNewWard();
        } else {
            return $this->billingOldWard();
        }
    }

    public function billingDistrict()
    {
        if ($this->billing_address_system === 'old') {
            return $this->billingOldDistrict();
        }
        return null;
    }

    // Accessors để lấy địa chỉ đầy đủ
    public function getShippingFullAddressAttribute()
    {
        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shippingWard?->name,
            $this->shippingDistrict?->name,
            $this->shippingProvince?->name,
            $this->shipping_country,
        ]);
        
        return implode(', ', $parts);
    }

    public function getShippingFullAddressWithTypeAttribute()
    {
        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shippingWard?->name_with_type,
            $this->shippingDistrict?->name_with_type,
            $this->shippingProvince?->name_with_type,
            $this->shipping_country,
        ]);
        
        return implode(', ', $parts);
    }

    public function getBillingFullAddressAttribute()
    {
        if (!$this->billing_address_line1) {
            return null;
        }

        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billingWard?->name,
            $this->billingDistrict?->name,
            $this->billingProvince?->name,
            $this->billing_country,
        ]);
        
        return implode(', ', $parts);
    }

    public function getBillingFullAddressWithTypeAttribute()
    {
        if (!$this->billing_address_line1) {
            return null;
        }

        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billingWard?->name_with_type,
            $this->billingDistrict?->name_with_type,
            $this->billingProvince?->name_with_type,
            $this->billing_country,
        ]);
        
        return implode(', ', $parts);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActiveOrders($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RETURNED
        ]);
    }

    public function scopeByProvince($query, $provinceCode)
    {
        return $query->where(function($q) use ($provinceCode) {
            $q->where('shipping_new_province_code', $provinceCode)
              ->orWhere('shipping_old_province_code', $provinceCode);
        });
    }

    public function scopeByWard($query, $wardCode)
    {
        return $query->where(function($q) use ($wardCode) {
            $q->where('shipping_new_ward_code', $wardCode)
              ->orWhere('shipping_old_ward_code', $wardCode);
        });
    }

    // Scope để lọc theo hệ thống
    public function scopeNewSystem($query)
    {
        return $query->where('shipping_address_system', 'new');
    }

    public function scopeOldSystem($query)
    {
        return $query->where('shipping_address_system', 'old');
    }
}