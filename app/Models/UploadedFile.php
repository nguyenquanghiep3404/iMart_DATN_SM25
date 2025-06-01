<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get the parent attachable model (Product, Category, User, etc.).
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
}