<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description', 'type', 'value', 'max_uses', 'max_uses_per_user',
        'min_order_amount', 'start_date', 'end_date', 'status', 'is_public', 'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
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
        if ($this->type === 'percentage') {
            return round($subtotal * $this->value / 100, 2);
        }

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        return 0;
    }
}
