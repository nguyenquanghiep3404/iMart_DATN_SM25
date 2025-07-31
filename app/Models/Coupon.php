<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_discount_amount',
        'max_uses',
        'max_uses_per_user',
        'min_order_amount',
        'start_date',
        'end_date',
        'status',
        'is_public',
        'user_id',
        'created_by',
        'deleted_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'min_order_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ✅ Kiểm tra mã đã hết hạn chưa
    public function expired(): bool
    {
        return $this->end_date && $this->end_date->lt(Carbon::now());
    }

    // ✅ Kiểm tra user đã dùng chưa
    public function usedBy($userId): bool
    {
        return $this->usages()->where('user_id', $userId)->exists();
    }

    // ✅ Tính giảm giá dựa trên loại mã (fixed hoặc percentage)
    public function calculateDiscount($subtotal): float
    {
        // Kiểm tra tổng tiền có đủ điều kiện không
        if ($this->min_order_amount && $subtotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = round($subtotal * $this->value / 100, 2);

            // Giới hạn số tiền giảm nếu có
            if ($this->max_discount_amount) {
                return min($discount, $this->max_discount_amount);
            }

            return $discount;
        }

        if ($this->type === 'fixed_amount' || $this->type === 'fixed') {
            return min($this->value, $subtotal); // không vượt quá tổng tiền
        }

        return 0;
    }
}
