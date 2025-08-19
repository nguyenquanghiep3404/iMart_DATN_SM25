<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'package_status_history';

    protected $fillable = [
        'package_id',
        'status',
        'timestamp',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Relationship với Package
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Relationship với User (người tạo)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope để lấy lịch sử theo package
     */
    public function scopeForPackage($query, $packageId)
    {
        return $query->where('package_id', $packageId);
    }

    /**
     * Scope để lấy lịch sử theo trạng thái
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope để sắp xếp theo thời gian
     */
    public function scopeOrderByTimestamp($query, $direction = 'desc')
    {
        return $query->orderBy('timestamp', $direction);
    }

    /**
     * Lấy lịch sử mới nhất của package
     */
    public static function getLatestForPackage($packageId)
    {
        return static::forPackage($packageId)
            ->orderByTimestamp('desc')
            ->first();
    }

    /**
     * Lấy tất cả lịch sử của package theo thứ tự thời gian
     */
    public static function getHistoryForPackage($packageId)
    {
        return static::forPackage($packageId)
            ->orderByTimestamp('asc')
            ->get();
    }
}
