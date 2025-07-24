<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TradeInItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_variant_id', 'store_location_id', 'type', 'sku', 'condition_grade',
        'condition_description', 'selling_price', 'imei_or_serial', 'status'
    ];

    // --- Relationships ---

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    public function images()
    {
        // Quan hệ đa hình để lưu trữ ảnh
        return $this->morphMany(UploadedFile::class, 'attachable');
    }

    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'cartable');
    }

    // --- Accessor for Cover Image URL (ĐÃ SỬA LỖI) ---

    /**
     * Lấy URL của ảnh đại diện.
     *
     * Phương thức này được tối ưu để hoạt động với eager loading.
     * Nó sẽ kiểm tra xem collection 'images' đã được tải chưa.
     * Nếu rồi, nó sẽ tìm ảnh chính từ collection đó để tránh truy vấn lại CSDL.
     *
     * @return string
     */
    public function getCoverImageUrlAttribute(): string
    {
        $image = null;

        // Kiểm tra xem mối quan hệ 'images' đã được tải sẵn (eager loaded) hay chưa
        if ($this->relationLoaded('images')) {
            // Nếu đã tải, tìm ảnh chính từ collection đã có sẵn
            $image = $this->images->firstWhere('type', 'primary_image');
            // Nếu không có ảnh chính, lấy ảnh đầu tiên theo thứ tự
            if (!$image) {
                $image = $this->images->sortBy('order')->first();
            }
        } else {
            // Nếu chưa tải, thực hiện truy vấn như bình thường
            $image = $this->images()->where('type', 'primary_image')->first()
                  ?? $this->images()->orderBy('order', 'asc')->first();
        }

        // Trả về URL nếu tìm thấy ảnh và file tồn tại
        if ($image && Storage::disk('public')->exists($image->path)) {
            return Storage::url($image->path);
        }

        // Trả về ảnh placeholder mặc định nếu không có ảnh
        return asset('admin-assets/images/placeholder.png');
    }
}
