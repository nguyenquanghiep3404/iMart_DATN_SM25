<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Đã import

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'cartable_id',
        'cartable_type',
        'quantity',
        'price',
        'added_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2', // Hoặc kiểu dữ liệu phù hợp với giá tiền của bạn
        'added_at' => 'datetime',
    ];

    /**
     * Get the cart that the item belongs to.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product variant associated with the cart item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function cartable()
    {
        return $this->morphTo();
    }

    // Accessor để tính thành tiền cho mục này
    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->price ?? ($this->cartable->price ?? 0));
    }
}
