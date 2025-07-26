<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\WardOld;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with(['ward', 'district', 'province'])->latest()->paginate(10);
        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'provinces' => ProvinceOld::all(),
            'districts' => DistrictOld::all(),
            'wards' => WardOld::all(),
        ]);
    }



    // App\Http\Controllers\Admin\SupplierController.php
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers')->whereNull('deleted_at')],
                'phone' => ['nullable', 'regex:/^0\d{9}$/', Rule::unique('suppliers')->whereNull('deleted_at')],
                'province_code' => ['required', 'exists:provinces_old,code'],
                'district_code' => ['required', 'exists:districts_old,code'],
                'ward_code' => ['required', 'exists:wards_old,code'],
                'address_line' => ['required', 'string', 'max:255'],
            ], $this->validationMessages());


            $supplier = Supplier::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm nhà cung cấp thành công!',
                'data' => $supplier
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }




    public function update(Request $request, $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers')->ignore($supplier->id)->whereNull('deleted_at')],
                'phone' => ['nullable', 'regex:/^0\d{9}$/', Rule::unique('suppliers')->ignore($supplier->id)->whereNull('deleted_at')],
                'province_code' => ['required', 'exists:provinces_old,code'],
                'district_code' => ['required', 'exists:districts_old,code'],
                'ward_code' => ['required', 'exists:wards_old,code'],
                'address_line' => ['required', 'string', 'max:255'],
            ], $this->validationMessages());

            $supplier->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhà cung cấp thành công!'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }



    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Xóa nhà cung cấp thành công.');
    }

    public function trash()
    {
        $suppliers = Supplier::onlyTrashed()
            ->with(['ward', 'district', 'province'])
            ->latest()
            ->paginate(10);

        return view('admin.suppliers.trash', [
            'suppliers' => $suppliers,
            'provinces' => ProvinceOld::all(),
            'districts' => DistrictOld::all(),
            'wards' => WardOld::all(),
        ])->with('success', session('success'));
    }


    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()
            ->route('admin.suppliers.trash')
            ->with('success', 'Khôi phục nhà cung cấp thành công.');
    }

    public function forceDelete($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->forceDelete();

        return redirect()
            ->route('admin.suppliers.trash')
            ->with('success', 'Đã xóa vĩnh viễn nhà cung cấp.');
    }
    private function validationMessages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'name.max' => 'Tên nhà cung cấp không được vượt quá 255 ký tự.',
            'name.regex' => 'Tên nhà cung cấp chỉ được chứa chữ cái và khoảng trắng.',

            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng cho nhà cung cấp khác.',

            'phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'phone.unique' => 'Số điện thoại đã được sử dụng cho nhà cung cấp khác.',

            'province_code.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            'province_code.exists' => 'Tỉnh/Thành phố không hợp lệ.',

            'district_code.required' => 'Vui lòng chọn Quận/Huyện.',
            'district_code.exists' => 'Quận/Huyện không hợp lệ.',

            'ward_code.required' => 'Vui lòng chọn Phường/Xã.',
            'ward_code.exists' => 'Phường/Xã không hợp lệ.',

            'address_line.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'address_line.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
        ];
    }
}
