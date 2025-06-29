<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link_url',
        'position',
        'order',
        'status',
        'start_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Mối quan hệ đa hình cho ảnh banner (ví dụ: một cho desktop, một cho mobile)
    public function desktopImage()
    {
        return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'banner_desktop');
    }

    public function mobileImage()
    {
        return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'banner_mobile');
    }

    // Hoặc lấy tất cả ảnh nếu có nhiều loại
    public function images()
    {
        return $this->morphMany(UploadedFile::class, 'attachable');
    }
    public function scopeActiveAndValid(Builder $query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', Carbon::now());
            });
    }
}
