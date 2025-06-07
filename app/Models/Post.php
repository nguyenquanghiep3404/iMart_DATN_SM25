<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'user_id', 'post_category_id',
        'status', 'is_featured', 'view_count', 'meta_title', 'meta_description', 'meta_keywords', 'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function user() // Tác giả
    {
        return $this->belongsTo(User::class);
    }

    public function category() // Danh mục bài viết
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(PostTag::class, 'post_post_tag');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->orderBy('created_at');
    }

    // Mối quan hệ đa hình cho ảnh bìa bài viết
    public function coverImage()
    {
        return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'post_cover_image');
    }

    public function getCoverImageUrlAttribute()
    {
        if ($this->coverImage && $this->coverImage->path) {
            return asset('storage/' . $this->coverImage->path);
        }
        return 'https://via.placeholder.com/800x400?text=No+Cover';
    }
}