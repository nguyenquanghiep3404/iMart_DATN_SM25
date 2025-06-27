<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        'deleted_by', // Thêm deleted_by vào fillable
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
        'deleted_at' => 'datetime', // Nên cast cả cột này
    ];
    
    // Thêm $appends để Accessor luôn được thêm vào khi model chuyển thành array/JSON
    protected $appends = ['url', 'formatted_size', 'attachable_display'];

    /**
     * Lấy model cha mà file này được đính kèm (Product, ProductVariant, etc.).
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Lấy người dùng đã upload file.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lấy người dùng đã xóa file (soft delete).
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Accessor để lấy URL đầy đủ của file.
     */
    public function getUrlAttribute()
    {
        if ($this->path && $this->disk) {
            return Storage::disk($this->disk)->url($this->path);
        }
        return null;
    }

    /**
     * Accessor để lấy kích thước file đã được định dạng.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes === 0 || is_null($bytes)) {
            return '0 Bytes';
        }
        $k = 1024;
        $dm = 2;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, $k));
        return sprintf("%.{$dm}f %s", $bytes / pow($k, $i), $sizes[$i]);
    }

    /**
     * Accessor để hiển thị thông tin đối tượng được đính kèm.
     */
    public function getAttachableDisplayAttribute(): string
    {
        if ($this->attachable) {
            $modelName = class_basename($this->attachable_type);
            return sprintf('%s (ID: %d)', $modelName, $this->attachable_id);
        }
        return 'Không đính kèm';
    }

    /**
     * Gộp tất cả các xử lý sự kiện của model vào một nơi.
     */
    protected static function booted(): void
    {
        // Tự động gán user_id khi tạo file mới
        static::creating(function (UploadedFile $model) {
            if (Auth::check() && !$model->user_id) {
                $model->user_id = Auth::id();
            }
        });

        // Xử lý khi model bị xóa
        static::deleting(function (UploadedFile $file) {
            // CHỈ xóa file vật lý khi model bị XÓA VĨNH VIỄN (force delete)
            if ($file->isForceDeleting()) {
                try {
                    if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                        Storage::disk($file->disk)->delete($file->path);
                        Log::info("File vật lý đã được xóa vĩnh viễn: {$file->path}");
                    }
                } catch (\Exception $e) {
                    Log::error("Không thể xóa vĩnh viễn file vật lý {$file->path}: " . $e->getMessage());
                }
            } else {
                 // Ghi lại người xóa khi thực hiện soft delete
                 if (Auth::check() && is_null($file->deleted_by)) {
                    $file->deleted_by = Auth::id();
                    $file->saveQuietly(); // Lưu mà không kích hoạt lại event
                }
            }
        });
    }
}
