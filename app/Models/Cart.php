<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get all of the products in the cart.
     * (Relationship thông qua cart_items và product_variants)
     */
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'cart_items')
                    ->withPivot('quantity', 'price', 'added_at') // Lấy thêm các cột từ bảng trung gian
                    ->withTimestamps(); // Nếu bảng trung gian có timestamps (created_at, updated_at cho cart_item)
    }

    // Accessor để tính tổng tiền của giỏ hàng (ví dụ)
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * ($item->price ?? $item->productVariant->price); // Ưu tiên giá lúc thêm vào giỏ
        });
    }

    // Accessor để đếm tổng số sản phẩm (không phải số lượng từng loại)
    public function getTotalItemsAttribute()
    {
        return $this->items->count();
    }

    // Accessor để đếm tổng số lượng sản phẩm
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function abandonedCart()
    {
        return $this->hasOne(AbandonedCart::class);
    }
    protected static function booted()
    {
        static::saved(function ($cart) {
            $cart->touch();  // Cập nhật 'updated_at' mỗi khi giỏ hàng được lưu
        });
    }
}
