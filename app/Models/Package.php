<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_fulfillment_id',
        'package_code',
        'description',
        'shipping_carrier',
        'tracking_code',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Các trạng thái có thể có của gói hàng theo luồng nghiệp vụ mới
    const STATUS_PENDING_CONFIRMATION = 'pending_confirmation'; // Chờ xác nhận
    const STATUS_PROCESSING = 'processing'; // Đang xử lý
    const STATUS_PACKED = 'packed'; // Đã đóng gói xong
    const STATUS_AWAITING_SHIPMENT_ASSIGNED = 'awaiting_shipment_assigned'; // Chờ vận chuyển: đã gán shipper
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery'; // Đang giao hàng
    const STATUS_DELIVERED = 'delivered'; // Giao hàng thành công
    const STATUS_CANCELLED = 'cancelled'; // Hủy
    const STATUS_FAILED_DELIVERY = 'failed_delivery'; // Giao thất bại
    const STATUS_RETURNED = 'returned'; // Trả hàng

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING_CONFIRMATION => 'Chờ xác nhận',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_PACKED => 'Chờ vận chuyển: đã đóng gói xong',
            self::STATUS_AWAITING_SHIPMENT_ASSIGNED => 'Đã gán shipper: chờ vận chuyển',
            self::STATUS_OUT_FOR_DELIVERY => 'Đang giao hàng',
            self::STATUS_DELIVERED => 'Giao hàng thành công',
            self::STATUS_CANCELLED => 'Hủy',
            self::STATUS_FAILED_DELIVERY => 'Giao thất bại',
            self::STATUS_RETURNED => 'Trả hàng',
        ];
    }

    /**
     * Relationship với OrderFulfillment
     */
    public function orderFulfillment(): BelongsTo
    {
        return $this->belongsTo(OrderFulfillment::class);
    }

    /**
     * Relationship với OrderFulfillmentItems
     */
    public function fulfillmentItems(): HasMany
    {
        return $this->hasMany(OrderFulfillmentItem::class);
    }

    /**
     * Relationship với PackageStatusHistory
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(PackageStatusHistory::class);
    }

    /**
     * Cập nhật trạng thái gói hàng và lưu lịch sử
     */
    public function updateStatus($newStatus, $notes = null, $userId = null)
    {
        $oldStatus = $this->status;
        
        if ($oldStatus !== $newStatus) {
            $this->status = $newStatus;
            
            // Cập nhật thời gian shipped_at và delivered_at
            if ($newStatus === self::STATUS_OUT_FOR_DELIVERY && !$this->shipped_at) {
                $this->shipped_at = now();
            }
            
            if ($newStatus === self::STATUS_DELIVERED && !$this->delivered_at) {
                $this->delivered_at = now();
            }
            
            $this->save();
            
            // Lưu lịch sử thay đổi trạng thái
            $this->statusHistory()->create([
                'status' => $newStatus,
                'timestamp' => now(),
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        }
    }

    /**
     * Tạo mã gói hàng tự động
     */
    public static function generatePackageCode($orderFulfillmentId)
    {
        $prefix = 'PKG';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $orderFulfillmentId . $timestamp . $random;
    }

    /**
     * Kiểm tra xem gói hàng có thể cập nhật trạng thái không
     */
    public function canUpdateStatus($newStatus)
    {
        $statusFlow = [
            self::STATUS_PENDING_CONFIRMATION => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_PACKED, self::STATUS_CANCELLED],
            self::STATUS_PACKED => [self::STATUS_AWAITING_SHIPMENT_ASSIGNED, self::STATUS_CANCELLED],
            self::STATUS_AWAITING_SHIPMENT_ASSIGNED => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_CANCELLED],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_FAILED_DELIVERY, self::STATUS_CANCELLED],
            self::STATUS_DELIVERED => [], // Trạng thái cuối
            self::STATUS_CANCELLED => [], // Trạng thái cuối
            self::STATUS_FAILED_DELIVERY => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_RETURNED],
            self::STATUS_RETURNED => [], // Trạng thái cuối
        ];

        return in_array($newStatus, $statusFlow[$this->status] ?? []);
    }
}
