<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Http\Requests\AttributeValueRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttributeController extends Controller
{
    use AuthorizesRequests;
    // Phân quyền
     public function __construct()
    {
        // Tự động phân quyền cho tất cả các phương thức CRUD
        $this->authorizeResource(Attribute::class, 'attribute');
    }
    /**
     * Hiển thị danh sách các thuộc tính.
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
     * Hiển thị form để tạo mới một thuộc tính.
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Lưu một thuộc tính mới vào cơ sở dữ liệu.
     */
    public function store(AttributeRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $attributeData = $validatedData;
            // Tự động tạo slug từ name nếu slug không được cung cấp
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
     * Hiển thị chi tiết một thuộc tính và các giá trị của nó.
     */
    public function show(Attribute $attribute)
    {
        // Eager load các giá trị để tối ưu truy vấn
        $attribute->load('attributeValues');
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Hiển thị form để chỉnh sửa một thuộc tính.
     */
    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Cập nhật một thuộc tính trong cơ sở dữ liệu.
     */
    public function update(AttributeRequest $request, Attribute $attribute)
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
     * Xóa một thuộc tính khỏi cơ sở dữ liệu.
     */
    public function destroy(Attribute $attribute)
    {
        // Cân nhắc thêm logic kiểm tra xem thuộc tính có đang được sản phẩm nào sử dụng không trước khi xóa.
        DB::beginTransaction();
        try {
            $attribute->delete(); // Giả định đã thiết lập 'on delete cascade' trong migration cho các giá trị.
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
     * Lưu một giá trị thuộc tính mới.
     */
    public function storeValue(AttributeValueRequest $request, Attribute $attribute)
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
     * Cập nhật một giá trị thuộc tính.
     * Logic để hiển thị lỗi đúng form đã được xử lý trong file `show.blade.php`.
     */
    public function updateValue(AttributeValueRequest $request, Attribute $attribute, AttributeValue $value)
    {
        // Lớp bảo vệ để đảm bảo giá trị này thuộc đúng thuộc tính đang xem.
        if ($value->attribute_id !== $attribute->id) {
            abort(404);
        }

        try {
            $value->update($request->validated());
            return redirect()->route('admin.attributes.show', $attribute->id)
                             ->with('success_value', 'Giá trị thuộc tính đã được cập nhật.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật giá trị thuộc tính: ' . $e->getMessage());
            // withInput() sẽ tự động gửi lại các input cũ, bao gồm cả 'attribute_value_id' mà chúng ta đã thêm.
            return back()->withInput()->with('error_value', 'Đã có lỗi xảy ra khi cập nhật giá trị.');
        }
    }

    /**
     * Xóa một giá trị thuộc tính.
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

