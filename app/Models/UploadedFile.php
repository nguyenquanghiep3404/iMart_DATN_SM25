<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; 

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'type',
        'order',
        'alt_text',
        'user_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Lấy model cha mà file này được đính kèm (Product, ProductVariant, Category, etc.).
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    public function user() // Người upload
    {
        return $this->belongsTo(User::class);
    }

    // Accessor để lấy URL đầy đủ
    public function getUrlAttribute()
    {
        if ($this->path && $this->disk) {
            return Storage::disk($this->disk)->url($this->path);
        }
        return null;
    }
    /**
     * Xử lý xóa file vật lý khi model bị xóa.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check() && !$model->user_id) {
                $model->user_id = Auth::id();
            }
        });

        static::deleting(function ($uploadedFile) {
            if ($uploadedFile->disk && $uploadedFile->path) {
                Storage::disk($uploadedFile->disk)->delete($uploadedFile->path);
            }
        });
    }
}