<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền gửi request hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug' . ($categoryId ? ',' . $categoryId : ''),
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $categoryId,
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'specification_groups' => 'nullable|array',
            'specification_groups.*' => 'exists:specification_groups,id',
        ];
    }

    /**
     * Thông báo lỗi.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.string' => 'Tên danh mục phải là chuỗi.',
            'name.max' => 'Tên danh mục không được vượt quá :max ký tự.',

            'slug.string' => 'Slug phải là chuỗi.',
            'slug.unique' => 'Slug đã được sử dụng.',

            'parent_id.exists' => 'Danh mục cha không hợp lệ.',
            'parent_id.not_in' => 'Danh mục không thể là con của chính nó.',

            'description.required' => 'Mô tả là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi.',

            'order.integer' => 'Thứ tự phải là số nguyên.',

            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'meta_title.string' => 'Tiêu đề SEO phải là chuỗi.',
            'meta_title.max' => 'Tiêu đề SEO không được vượt quá :max ký tự.',

            'meta_description.string' => 'Mô tả SEO phải là chuỗi.',
            'meta_keywords.string' => 'Từ khóa SEO phải là chuỗi.',
            'meta_keywords.max' => 'Từ khóa SEO không được vượt quá :max ký tự.',
        ];
    }
}
