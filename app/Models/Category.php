<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'status',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order')->orderBy('name');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Mối quan hệ đa hình cho ảnh danh mục
    // public function image()
    // {
    //     return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'category_image');
    // }
    public function images()
{
    return $this->morphMany(UploadedFile::class, 'attachable');
}

    // Helper để lấy URL ảnh
    public function getImageUrlAttribute()
    {
        if ($this->image && $this->image->path) {
            return asset('storage/' . $this->image->path);
        }
        return 'https://via.placeholder.com/150?text=No+Image'; // Placeholder
    }
}
