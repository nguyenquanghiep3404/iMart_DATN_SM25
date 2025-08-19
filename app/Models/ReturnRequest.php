<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'user_id',
        'return_code',
        'reason',
        'reason_details',
        'status',
        'rejection_reason',
        'refund_method',
        'refund_amount',
        'refunded_points',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'admin_note',
        'approved_by',
        'refund_processed_by',
        'refunded_at'
    ];

    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'rejected' => 'Từ chối',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }
    public function getReasonTextAttribute()
    {
        return match ($this->reason) {
            'defective' => 'Sản phẩm bị lỗi do nhà sản xuất',
            'wrong_item' => 'Giao sai sản phẩm',
            'not_as_described' => 'Không đúng như mô tả',
            'changed_mind' => 'Thay đổi ý định (có thể áp dụng phí)',
            'other' => 'Lý do khác',
            default => 'Không xác định',
        };
    }

    public function getRefundMethodTextAttribute()
    {
        return match ($this->refund_method) {
            'points' => 'Hoàn bằng điểm',
            'bank' => 'Chuyển khoản ngân hàng',
            'coupon' => 'Hoàn bằng mã giảm giá',
            default => 'Không xác định',
        };
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class);
    }
    public function files()
    {
        return $this->morphMany(UploadedFile::class, 'attachable');
    }
    public function refundProcessor()
    {
        return $this->belongsTo(User::class, 'refund_processed_by');
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function orderItem()
    {
        return $this->hasOne(ReturnItem::class, 'return_request_id')->with('orderItem.variant.product.coverImage');
    }

    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }
    public function variant()
    {
        return $this->hasOneThrough(
            ProductVariant::class,
            OrderItem::class,
            'id',            // foreign key on OrderItem
            'id',            // foreign key on ProductVariant
            'order_item_id', // local key on ReturnItem
            'variant_id'     // local key on OrderItem
        );
    }
}
