<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'user_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'status',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    // Mối quan hệ đa hình cho ảnh đánh giá
    public function images()
    {
        return $this->morphMany(UploadedFile::class, 'attachable')->where('type', 'review_image')->orderBy('order');
    }

    public function replies() // Nếu có hệ thống phản hồi bình luận
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->orderBy('created_at');
    }
}