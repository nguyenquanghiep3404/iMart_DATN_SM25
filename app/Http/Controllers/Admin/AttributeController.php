<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeController extends Controller
{
    /**
     * Display a listing of the attributes.
     */
    public function index(Request $request)
    {
        $query = Attribute::withCount('attributeValues')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $attributes = $query->paginate(15)->withQueryString();
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'slug' => 'nullable|string|max:255|unique:attributes,slug',
            'display_type' => 'required|in:select,radio,color_swatch', // Thêm các kiểu hiển thị khác nếu cần
        ]);

        DB::beginTransaction();
        try {
            $attributeData = $validatedData;
            $attributeData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

            Attribute::create($attributeData);

            DB::commit();
            return redirect()->route('admin.attributes.index')
                             ->with('success', 'Thuộc tính đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo thuộc tính.');
        }
    }

    /**
     * Display the specified attribute and its values.
     * This page will be used to manage attribute values.
     */
    public function show(Attribute $attribute)
    {
        // Eager load attribute values, có thể phân trang nếu danh sách giá trị quá dài
        $attribute->load('attributeValues');
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes')->ignore($attribute->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes')->ignore($attribute->id)],
            'display_type' => 'required|in:select,radio,color_swatch',
        ]);

        DB::beginTransaction();
        try {
            $attributeData = $validatedData;
            $attributeData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

            $attribute->update($attributeData);

            DB::commit();
            return redirect()->route('admin.attributes.index')
                             ->with('success', 'Thuộc tính đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật thuộc tính.');
        }
    }

    /**
     * Remove the specified attribute from storage.
     */
    public function destroy(Attribute $attribute)
    {
        // Kiểm tra xem thuộc tính có đang được sử dụng bởi bất kỳ biến thể sản phẩm nào không
        // Logic này cần được triển khai nếu bạn muốn ngăn xóa thuộc tính đang dùng
        // Ví dụ: if ($attribute->productVariants()->count() > 0) { ... }

        DB::beginTransaction();
        try {
            // Xóa tất cả các giá trị thuộc tính liên quan trước (nếu có ràng buộc khóa ngoại)
            // Hoặc nếu CSDL của bạn có 'on delete cascade' thì không cần dòng này.
            $attribute->attributeValues()->delete();
            $attribute->delete();

            DB::commit();
            return redirect()->route('admin.attributes.index')
                             ->with('success', 'Thuộc tính và các giá trị liên quan đã được xóa.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa thuộc tính: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa thuộc tính.');
        }
    }

    /**
     * Store a new attribute value for the specified attribute.
     */
    public function storeValue(Request $request, Attribute $attribute)
    {
        $validatedData = $request->validate([
            'value' => ['required', 'string', 'max:255',
                // Đảm bảo giá trị là duy nhất cho thuộc tính này
                Rule::unique('attribute_values')->where(function ($query) use ($attribute) {
                    return $query->where('attribute_id', $attribute->id);
                })
            ],
            'meta' => 'nullable|string|max:255', // Ví dụ: mã màu hex cho color_swatch
        ]);

        try {
            $attribute->attributeValues()->create($validatedData);
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được thêm thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm giá trị thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error_value', 'Đã có lỗi xảy ra khi thêm giá trị thuộc tính.');
        }
    }

    /**
     * Show the form for editing an attribute value. (Sẽ dùng modal trong show)
     * Nếu muốn trang riêng thì tạo view và route riêng.
     */
    // public function editValue(Attribute $attribute, AttributeValue $value) { ... }


    /**
     * Update the specified attribute value in storage.
     */
    public function updateValue(Request $request, Attribute $attribute, AttributeValue $value)
    {
        if ($value->attribute_id !== $attribute->id) {
            abort(404); // Hoặc xử lý lỗi khác
        }

        $validatedData = $request->validate([
            'edit_value_name_' . $value->id => ['required', 'string', 'max:255',
                Rule::unique('attribute_values', 'value')->where(function ($query) use ($attribute) {
                    return $query->where('attribute_id', $attribute->id);
                })->ignore($value->id)
            ],
            'edit_value_meta_' . $value->id => 'nullable|string|max:255',
        ]);

        try {
            $value->update([
                'value' => $validatedData['edit_value_name_' . $value->id],
                'meta' => $validatedData['edit_value_meta_' . $value->id],
            ]);
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được cập nhật.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật giá trị thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error_value', 'Đã có lỗi xảy ra khi cập nhật giá trị thuộc tính.');
        }
    }


    /**
     * Remove the specified attribute value from storage.
     */
    public function destroyValue(Attribute $attribute, AttributeValue $value)
    {
        if ($value->attribute_id !== $attribute->id) {
            abort(404);
        }

        // Kiểm tra xem giá trị thuộc tính có đang được sử dụng bởi biến thể sản phẩm nào không
        // if ($value->productVariants()->count() > 0) { ... }

        try {
            $value->delete();
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được xóa.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa giá trị thuộc tính: ' . $e->getMessage());
            return back()->with('error_value', 'Đã có lỗi xảy ra khi xóa giá trị thuộc tính.');
        }
    }
}
