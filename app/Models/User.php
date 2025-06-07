<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'status',
        'last_login_at',
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
        return $this->morphOne(UploadedFile::class, 'attachable')->where('type', 'avatar');
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
        // Trả về ảnh placeholder nếu không có avatar
        return asset('adminlte/dist/img/avatar_placeholder.png'); // Hoặc một ảnh placeholder khác
    }


    public function hasRole($roleName)
    {
        if (is_string($roleName)) {
            return $this->roles->contains('name', $roleName);
        }
        if (is_array($roleName)) {
            foreach ($roleName as $rName) {
                if ($this->roles->contains('name', $rName)) {
                    return true;
                }
            }
            return false;
        }
        return $this->roles->contains('id', $roleName->id);
    }

    public function hasPermissionTo($permissionName)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }
        return false;
    }
}
