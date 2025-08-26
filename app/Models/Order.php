<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\CancellationRequest;

class Order extends Model
{
    use HasFactory;

    // Status constants - Rút gọn theo yêu cầu
    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_EXTERNAL_SHIPPING = 'external_shipping';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED_DELIVERY = 'failed_delivery';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLATION_REQUESTED = 'cancellation_requested';

    // Payment status constants
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $fillable = [
        'user_id',
        'guest_id',
        'order_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_zip_code',
        'shipping_country',
        'shipping_old_province_code',
        'shipping_old_district_code',
        'shipping_old_ward_code',
        'billing_address_line1',
        'billing_address_line2',
        'billing_zip_code',
        'billing_country',
        'billing_old_province_code',
        'billing_old_district_code',
        'billing_old_ward_code',
        'sub_total',
        'shipping_fee',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_method',
        'payment_status',
        'shipping_method',
        'status',
        'notes_from_customer',
        'notes_for_shipper',
        'admin_note',
        'desired_delivery_date',
        'desired_delivery_time_slot',
        'processed_by',
        'shipped_by',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'failed_delivery_reason',
        'ip_address',
        'user_agent',
        'store_location_id',
        'confirmed_at',
        'external_shipping_assigned_at',
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
            self::STATUS_OUT_FOR_DELIVERY => 'Đang giao hàng',
            self::STATUS_EXTERNAL_SHIPPING => 'Giao bởi đơn vị thứ 3',
            self::STATUS_DELIVERED => 'Giao hàng thành công',
            self::STATUS_CANCELLED => 'Hủy',
            self::STATUS_FAILED_DELIVERY => 'Giao hàng thất bại',
            self::STATUS_RETURNED => 'Trả hàng',
            self::STATUS_CANCELLATION_REQUESTED => 'Yêu cầu hủy'
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
            self::STATUS_FAILED_DELIVERY,
            self::STATUS_RETURNED
        ]);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_PENDING_CONFIRMATION,
            self::STATUS_PROCESSING,
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

    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    public function orderFulfillments()
    {
        return $this->hasMany(OrderFulfillment::class);
    }

    public function fulfillments()
    {
        return $this->hasMany(OrderFulfillment::class);
    }

    // Địa chỉ mới - Shipping
    public function shippingNewProvince()
    {
        return $this->belongsTo(Province::class, 'shipping_new_province_code', 'code');
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

    // Hệ thống CŨ - Billing
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
        // Hệ thống mới không có district, trả về relationship rỗng
        return $this->belongsTo(DistrictOld::class, 'shipping_old_district_code', 'code');
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
        // Hệ thống mới không có district, trả về relationship rỗng
        return $this->belongsTo(DistrictOld::class, 'billing_old_district_code', 'code');
    }

    // Accessors để lấy địa chỉ đầy đủ
    public function getShippingFullAddressAttribute()
    {
        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shippingWard?->name,
            $this->shipping_address_system === 'old' ? $this->shippingDistrict?->name : null,
            $this->shippingProvince?->name,
        ]);

        return implode(', ', $parts);
    }

    public function getShippingFullAddressWithTypeAttribute()
    {
        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shippingWard?->name_with_type,
            $this->shipping_address_system === 'old' ? $this->shippingDistrict?->name_with_type : null,
            $this->shippingProvince?->name_with_type,
        ]);

        return implode(', ', $parts);
    }

    public function getBillingFullAddressAttribute()
    {
        if (!$this->billing_address_line1) {
            return '';
        }

        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billingWard?->name,
            $this->billing_address_system === 'old' ? $this->billingDistrict?->name : null,
            $this->billingProvince?->name,
        ]);

        return implode(', ', $parts);
    }

    public function getBillingFullAddressWithTypeAttribute()
    {
        if (!$this->billing_address_line1) {
            return '';
        }

        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billingWard?->name_with_type,
            $this->billing_address_system === 'old' ? $this->billingDistrict?->name_with_type : null,
            $this->billingProvince?->name_with_type,
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
            self::STATUS_FAILED_DELIVERY,
            self::STATUS_RETURNED
        ]);
    }

    public function scopeByProvince($query, $provinceCode)
    {
        return $query->where('shipping_old_province_code', $provinceCode);
    }

    public function scopeByWard($query, $wardCode)
    {
        return $query->where('shipping_old_ward_code', $wardCode);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Định dạng cho ngày giao hàng
    public function getFormattedDeliveryDateAttribute()
    {
        if (!$this->desired_delivery_date) {
            return '';
        }
        // Nếu ngày đã được lưu theo định dạng d/m/Y thì trả về trực tiếp
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $this->desired_delivery_date)) {
            return $this->desired_delivery_date;
        }
        // Kiểm tra xem có phải là ngày tháng hợp lệ không
        try {
            // Nếu ngày được lưu theo định dạng Y-m-d, format lại thành d/m/Y
            return Carbon::parse($this->desired_delivery_date)->format('d/m/Y');
        } catch (\Exception $e) {
            // Nếu không parse được, trả về giá trị gốc hoặc chuỗi rỗng
            return $this->desired_delivery_date;
        }
    }
    public function returnRequests()
    {
        return $this->hasMany(\App\Models\ReturnRequest::class);
    }

    // Phương thức để lấy mã đơn hàng
    public function getCode()
    {
        return $this->order_code;
    }

    public function packages()
    {
        return $this->hasManyThrough(Package::class, OrderFulfillment::class, 'order_id', 'order_fulfillment_id');
    }
    public function cancellationRequest()
    {
        // Một đơn hàng chỉ có một yêu cầu hủy mới nhất
        return $this->hasOne(CancellationRequest::class)->latestOfMany();
    }

}
