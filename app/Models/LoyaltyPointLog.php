<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPointLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'points',
        'type',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function getVietnameseTypeAttribute(): string
    {
        return match ($this->type) {
            'earn' => 'Tích điểm',
            'spend' => 'Sử dụng',
            'refund' => 'Hoàn điểm',
            'manual_adjustment' => 'Điều chỉnh thủ công',
            'expire' => 'Hết hạn',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
