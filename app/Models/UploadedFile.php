<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
class UploadedFile extends Model
{
    use HasFactory, SoftDeletes;

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
    protected $guarded = [];

    protected $appends = ['url'];
    // Accessor để lấy URL đầy đủ
    public function getUrlAttribute()
    {
        if ($this->path && $this->disk) {
            return Storage::disk($this->disk)->url($this->path);
        }
        return null;
    }
    protected static function booted(): void
    {
        static::deleting(function (UploadedFile $uploadedFile) {
            // Chỉ xóa file vật lý nếu đây là hành động xóa vĩnh viễn
            if ($uploadedFile->isForceDeleting()) {
                if (Storage::disk($uploadedFile->disk)->exists($uploadedFile->path)) {
                    Storage::disk($uploadedFile->disk)->delete($uploadedFile->path);
                }
            }
        });
    }
public function deletedBy()
{
    // Mối quan hệ một-một đến model User
    return $this->belongsTo(User::class, 'deleted_by');
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

        // static::deleting(function ($file) {
        //     // Kiểm tra xem file có thực sự tồn tại trong storage không trước khi xóa
        //     if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
        //         try {
        //             Storage::disk($file->disk)->delete($file->path);
        //             Log::info("File vật lý đã được xóa thành công: {$file->path}");
        //         } catch (\Exception $e) {
        //             Log::error("Không thể xóa file vật lý {$file->path}: " . $e->getMessage());
        //         }
        //     } else {
        //          Log::warning("File vật lý không tồn tại để xóa: {$file->path}");
        //     }
        // });
    }
    public function getAttachableDisplayAttribute(): string
    {
        if ($this->attachable) {
            // Lấy tên class của model, ví dụ: "App\Models\Product" -> "Product"
            $modelName = class_basename($this->attachable_type);
            return sprintf('%s (ID: %d)', $modelName, $this->attachable_id);
        }
        return 'Không đính kèm';
    }
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes === 0) {
            return '0 Bytes';
        }
        $k = 1024;
        $dm = 2;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, $k));

        return sprintf("%.{$dm}f %s", $bytes / pow($k, $i), $sizes[$i]);
    }
}