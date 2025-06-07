<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest; // Import
use App\Http\Requests\AttributeValueRequest; // Import
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeController extends Controller
{
    /**
     * Display a listing of the attributes.
     */
    public function index(Request $request)
    {
        $query = Attribute::withCount('attributeValues')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $attributes = $query->paginate(10)->withQueryString();
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
    public function store(AttributeRequest $request) // <-- Sử dụng AttributeRequest
    {
        $validatedData = $request->validated();

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
     */
    public function show(Attribute $attribute)
    {
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
    public function update(AttributeRequest $request, Attribute $attribute) // <-- Sử dụng AttributeRequest
    {
        $validatedData = $request->validated();

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
        // Bạn có thể thêm logic kiểm tra thuộc tính đang được sử dụng ở đây
        DB::beginTransaction();
        try {
            $attribute->delete(); // Giả sử đã có 'on delete cascade' trong CSDL
            DB::commit();
            return redirect()->route('admin.attributes.index')
                             ->with('success', 'Thuộc tính và các giá trị liên quan đã được xóa.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa thuộc tính: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa thuộc tính.');
        }
    }

    //== CÁC PHƯƠNG THỨC QUẢN LÝ GIÁ TRỊ THUỘC TÍNH ==//

    /**
     * Store a new attribute value for the specified attribute.
     */
    public function storeValue(AttributeValueRequest $request, Attribute $attribute) // <-- Sử dụng AttributeValueRequest
    {
        try {
            $attribute->attributeValues()->create($request->validated());
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được thêm thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm giá trị thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error_value', 'Đã có lỗi xảy ra khi thêm giá trị.');
        }
    }

    /**
     * Update the specified attribute value in storage.
     */
    public function updateValue(AttributeValueRequest $request, Attribute $attribute, AttributeValue $value) // <-- Sử dụng AttributeValueRequest
    {
        // Kiểm tra này vẫn tốt để có thêm một lớp bảo vệ
        if ($value->attribute_id !== $attribute->id) {
            abort(404);
        }

        try {
            $value->update($request->validated());
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được cập nhật.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật giá trị thuộc tính: ' . $e->getMessage());
            return back()->withInput()->with('error_value', 'Đã có lỗi xảy ra khi cập nhật giá trị.');
        }
    }

    /**
     * Remove the specified attribute value from storage.
     */
    public function destroyValue(Request $request, Attribute $attribute, AttributeValue $value)
    {
        if ($value->attribute_id !== $attribute->id) {
            abort(404);
        }
        
        // Bạn có thể thêm logic kiểm tra giá trị đang được sử dụng ở đây
        try {
            $value->delete();
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được xóa.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa giá trị thuộc tính: ' . $e->getMessage());
            return back()->with('error_value', 'Đã có lỗi xảy ra khi xóa giá trị.');
        }
    }
}