<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Category extends Model
{
    use HasFactory, SoftDeletes;

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
        'deleted_by',
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

    // Quan hệ với người xóa
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
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
