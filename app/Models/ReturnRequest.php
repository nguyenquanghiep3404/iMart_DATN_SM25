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
}
