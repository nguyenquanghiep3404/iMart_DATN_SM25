<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageProductBlock extends Model
{
    // Giữ nguyên tên bảng này
    protected $table = 'homepage_product_blocks';

    protected $fillable = [
        'title',
        'is_visible',
        'order',
    ];

    // ✅ Tên hàm đã được đổi để phản ánh đúng mối quan hệ
    public function productVariants()
    {
        // 💡 Sửa các tham số trong belongsToMany
        return $this->belongsToMany(
            ProductVariant::class, // Trỏ đến Model ProductVariant
            'homepage_block_product', // Tên bảng pivot
            'block_id', // Tên khóa ngoại của Model hiện tại trong bảng pivot
            'product_variant_id' // Tên khóa ngoại của Model ProductVariant trong bảng pivot
        )
        ->withPivot('order')
        ->withTimestamps()
        ->orderBy('pivot_order');
    }
}