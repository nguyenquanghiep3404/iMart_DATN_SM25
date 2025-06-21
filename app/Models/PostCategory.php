<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCategory extends Model
{
    use HasFactory;
    protected $table = 'post_categories';
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PostCategory::class, 'parent_id')->orderBy('name');
    }

    public function getAllChildrenIds()
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }

    public function getBreadcrumbLinksAttribute()
    {
        $breadcrumb = collect();
        $category = $this;

        while ($category) {
            $breadcrumb->prepend('<a href="' . route('admin.post-categories.show', $category->id) . '" class="text-blue-600 hover:underline">' . $category->name . '</a>');
            $category = $category->parent;
        }

        return $breadcrumb->join(' / ');
    }
}
