<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\PostCategory;

class NotDescendantOf implements ValidationRule
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nếu không có parent_id hoặc đang tạo mới (chưa có ID), thì bỏ qua
        if (!$value || !$this->categoryId) {
            return;
        }

        // Nếu cha mới là chính nó → lỗi
        if ($value == $this->categoryId) {
            $fail('Danh mục cha không được trùng với chính nó.');
            return;
        }

        // Tìm tất cả con cháu của danh mục hiện tại
        $descendants = $this->getAllDescendantIds($this->categoryId);

        // Nếu parent_id nằm trong danh sách con cháu → lỗi
        if (in_array($value, $descendants)) {
            $fail('Không thể chọn một danh mục con làm danh mục cha.');
        }
    }

    protected function getAllDescendantIds($id)
    {
        $children = PostCategory::where('parent_id', $id)->pluck('id')->toArray();
        $all = $children;

        foreach ($children as $childId) {
            $all = array_merge($all, $this->getAllDescendantIds($childId));
        }

        return $all;
    }
    public function storeWithChildren(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories,slug',
            'description' => 'nullable|string',
            'children' => 'nullable|array',
            'children.*' => 'nullable|string|max:255'
        ]);

        $parent = PostCategory::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? \Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'parent_id' => null
        ]);

        if (!empty($validated['children'])) {
            foreach ($validated['children'] as $childName) {
                if ($childName) {
                    PostCategory::create([
                        'name' => $childName,
                        'slug' => \Str::slug($childName),
                        'parent_id' => $parent->id
                    ]);
                }
            }
        }

        return redirect()->route('admin.categories_post.index')
                        ->with('success', 'Đã thêm danh mục cha và các danh mục con thành công!');
    }

}
