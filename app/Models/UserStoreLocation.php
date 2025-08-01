<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStoreLocation extends Model
{
    use HasFactory;
    protected $table = 'user_store_location';
    protected $fillable = [
        'user_id',
        'store_location_id',
    ];

    /**
     * Lấy thông tin người dùng thuộc về việc gán cửa hàng này.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy thông tin cửa hàng mà người dùng này được gán vào.
     */
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    /**
     * Scope để lấy các gán cho một người dùng cụ thể.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope để lấy các gán cho một cửa hàng cụ thể.
     */
    public function scopeForStore($query, $storeLocationId)
    {
        return $query->where('store_location_id', $storeLocationId);
    }

    /**
     * Kiểm tra xem một người dùng có được gán vào một cửa hàng cụ thể không.
     */
    public static function kiemTraNhanVienDuocGanVaoCuaHang($userId, $storeLocationId)
    {
        return static::where('user_id', $userId)
                    ->where('store_location_id', $storeLocationId)
                    ->exists();
    }

    /**
     * Kiểm tra xem nhân viên có thuộc cửa hàng không (dùng cho validation).
     */
    public static function nhanVienThuocCuaHang($userId, $storeLocationId)
    {
        return static::where('user_id', $userId)
                    ->where('store_location_id', $storeLocationId)
                    ->exists();
    }

    /**
     * Gán một người dùng vào một cửa hàng.
     */
    public static function ganNhanVienVaoCuaHang($userId, $storeLocationId)
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'store_location_id' => $storeLocationId,
        ]);
    }

    /**
     * Xóa một người dùng khỏi một cửa hàng.
     */
    public static function xoaNhanVienKhoiCuaHang($userId, $storeLocationId)
    {
        return static::where('user_id', $userId)
                    ->where('store_location_id', $storeLocationId)
                    ->delete();
    }

    /**
     * Lấy tất cả cửa hàng của một người dùng.
     */
    public static function layCuaHangCuaNhanVien($userId)
    {
        return static::with('storeLocation')
                    ->where('user_id', $userId)
                    ->get()
                    ->pluck('storeLocation');
    }

    /**
     * Lấy tất cả người dùng của một cửa hàng.
     */
    public static function layNhanVienCuaCuaHang($storeLocationId)
    {
        return static::with('user')
                    ->where('store_location_id', $storeLocationId)
                    ->get()
                    ->pluck('user');
    }
}
