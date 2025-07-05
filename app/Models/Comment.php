<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'parent_id',
        'content',
        'image_paths',
        'status',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    // --- Relationships ---

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Trả về phản hồi cấp 1
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at');
    }

    // Trả về tất cả phản hồi cấp sâu (đệ quy)
    public function repliesRecursive()
    {
        return $this->replies()->with(['user', 'repliesRecursive']);
    }

    // --- Helper attributes ---

    public function getReadableTypeAttribute()
    {
        return class_basename($this->commentable_type);
    }

    public function getCommentableDisplayAttribute()
    {
        $type = $this->readable_type;
        $name = $this->commentable->name ?? $this->commentable->title ?? 'Không rõ';
        return "{$type}: " . \Str::limit($name, 20) . " (ID: {$this->commentable_id})";
    }

    public function getReadableStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->image_paths)->map(fn($path) => asset('storage/' . $path))->toArray();
    }

    public static function getAvailableStatuses(): array
    {
        return ['pending', 'approved', 'rejected', 'spam'];
    }
}
