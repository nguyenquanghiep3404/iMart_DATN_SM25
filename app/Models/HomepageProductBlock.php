<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageProductBlock extends Model
{
    protected $table = 'homepage_product_blocks';

    protected $fillable = [
        'title',
        'is_visible',
        'order',
    ];

    // Một khối sản phẩm có nhiều sản phẩm (qua bảng trung gian)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'homepage_block_product', 'block_id', 'product_id')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }
}
