<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'status',
        'last_login_at',
        'password',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function posts() // Nếu user là tác giả bài viết
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function wishlist()
    {
        return $this->hasOne(Wishlist::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    public function images()
    {
        return $this->morphMany(UploadedFile::class, 'attachable');
    }

    // Mối quan hệ đa hình cho avatar
    public function avatar()
    {
        return $this->morphOne(\App\Models\UploadedFile::class, 'attachable')
            ->where('type', 'avatar')
            ->whereNull('deleted_at'); // Bỏ qua bản ghi đã soft delete
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
    // Helper để lấy đường dẫn avatar
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && $this->avatar->path) {
            return asset('storage/' . $this->avatar->path);
        }

        return null;
    }

    public function hasRole($role): bool
    {
        // Nếu đầu vào là một mảng các tên vai trò
        if (is_array($role)) {
            // Dùng whereIn để kiểm tra sự tồn tại của bất kỳ vai trò nào trong mảng
            // Đây là cách hiệu quả nhất, chỉ cần 1 truy vấn CSDL
            return $this->roles()->whereIn('name', $role)->exists();
        }

        // Nếu đầu vào là một đối tượng Role
        if ($role instanceof Role) {
            // Kiểm tra bằng id của đối tượng Role
            return $this->roles()->where('id', $role->id)->exists();
        }

        // Nếu đầu vào là một chuỗi (tên vai trò)
        // Đây là trường hợp phổ biến nhất, nên để ở cuối để tối ưu
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }

        return false;
    }


    public function hasPermissionTo($permissionNames): bool
    {
        // Đảm bảo đầu vào luôn là một mảng để xử lý đồng nhất
        $permissionNames = is_array($permissionNames) ? $permissionNames : [$permissionNames];

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionNames) {
                // Sửa lại: Dùng whereIn và biến $permissionNames
                $query->whereIn('name', $permissionNames);
            })
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        // Sử dụng whereIn để kiểm tra xem trong các vai trò của người dùng,
        // có tên nào nằm trong mảng $roles được truyền vào không.
        // exists() sẽ trả về true ngay khi tìm thấy một kết quả, rất hiệu quả.
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function shipperOrders()
    {
        // Quan hệ: Một user (shipper) có thể có nhiều đơn hàng
        return $this->hasMany(Order::class, 'shipped_by');
    }
    
}
