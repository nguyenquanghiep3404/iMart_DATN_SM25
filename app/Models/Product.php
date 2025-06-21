<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'short_description',
        'sku_prefix',
        'type',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'tags',
        'view_count',
        'warranty_information',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }
public function allUploadedFiles()
    {
        return $this->morphMany(UploadedFile::class, 'attachable');
    }

    // Mối quan hệ đa hình cho ảnh bìa
    public function coverImage()
    {
        return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'cover_image');
    }

    // Mối quan hệ đa hình cho ảnh gallery
    public function galleryImages()
    {
        return $this->morphMany(UploadedFile::class, 'attachable')->where('type', 'gallery_image')->orderBy('order');
    }

    public function reviews() // Đánh giá cho sản phẩm này (tổng hợp từ các biến thể)
    {
        return $this->hasManyThrough(Review::class, ProductVariant::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Helper để lấy URL ảnh bìa
    public function getCoverImageUrlAttribute()
    {
        if ($this->coverImage && $this->coverImage->path) {
            return asset('storage/' . $this->coverImage->path);
        }
        return 'https://via.placeholder.com/300?text=No+Cover'; // Placeholder
    }
    public function comments(): MorphMany
    {
        return $this->morphMany(\App\Models\Comment::class, 'commentable');
    }
}