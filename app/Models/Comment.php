<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'parent_id',
        'content',
        'status',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function getReadableTypeAttribute()
    {
        return class_basename($this->commentable_type);
    }

    public function getCommentableDisplayAttribute()
    {
        $type = $this->readable_type;
        $name = $this->commentable->name ?? $this->commentable->title ?? 'Không rõ';
        $name = \Str::limit($name, 20); 
        return "{$type}: {$name} (ID: {$this->commentable_id})";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }
    public static function getAvailableStatuses(): array
    {
        return ['pending', 'approved', 'rejected', 'spam'];
    }

    public function getReadableStatusAttribute(): string
    {
        return ucfirst($this->status);
    }
}