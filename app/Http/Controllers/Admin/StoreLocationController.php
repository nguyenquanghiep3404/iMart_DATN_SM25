<?php

namespace App\Http\Controllers\Admin; // Đảm bảo namespace này đúng

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use App\Models\ProvinceOld; // Đảm bảo các Model này đã được tạo và cấu hình đúng
use App\Models\DistrictOld;
use App\Models\WardOld;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;

class StoreLocationController extends Controller
{
    /**
     * Hiển thị trang quản lý cửa hàng với danh sách.
     * Eager load các mối quan hệ địa chỉ để hiển thị tên Tỉnh/Huyện/Xã.
     */
    public function index()
    {
        $storeLocations = StoreLocation::with(['province', 'district', 'ward'])
                                ->orderBy('id', 'desc')
                                ->get();

        $provinces = ProvinceOld::all();


        return view('admin.store_locations.index', compact('storeLocations', 'provinces'));
    }

    /**
     * Lấy danh sách quận/huyện dựa trên mã tỉnh.
     * THAY ĐỔI: SỬ DỤNG 'parent_code' để lọc trong bảng districts_old.
     */
    public function getDistrictsByProvince(Request $request)
    {
        $provinceCode = $request->input('province_code');
        // Thay đổi 'province_code' thành 'parent_code' để khớp với Model DistrictOld của bạn
        $districts = DistrictOld::where('parent_code', $provinceCode)->get(['code', 'name']);
        return response()->json($districts);
    }

    /**
     * Lấy danh sách phường/xã dựa trên mã quận/huyện.
     * THAY ĐỔI: SỬ DỤNG 'parent_code' để lọc trong bảng wards_old.
     */
    public function getWardsByDistrict(Request $request)
    {
        $districtCode = $request->input('district_code');
        // Thay đổi 'district_code' thành 'parent_code' để khớp với Model WardOld của bạn
        $wards = WardOld::where('parent_code', $districtCode)->get(['code', 'name']);
        return response()->json($wards);
    }

    /**
     * Lưu trữ (thêm mới) hoặc cập nhật (sửa) thông tin cửa hàng.
     * Validation kiểm tra code địa chỉ.
     */
    public function store(Request $request)
    {
        $id = $request->input('id');

        $rules = [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['store', 'warehouse', 'service_center'])],
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            // Validation cho các cột code địa chỉ
            'province_code' => 'required|string|exists:provinces_old,code',
            'district_code' => 'required|string|exists:districts_old,code',
            'ward_code' => 'required|string|exists:wards_old,code',
            'is_active' => 'boolean',
        ];

        $validatedData = $request->validate($rules);

        if ($id) {
            $storeLocation = StoreLocation::findOrFail($id);
            $storeLocation->update($validatedData);
            $message = 'Cửa hàng đã được cập nhật thành công!';
        } else {
            $storeLocation = StoreLocation::create($validatedData);
            $message = 'Cửa hàng đã được thêm mới thành công!';
        }

        Session::flash('success', $message);
        return redirect()->route('admin.store-locations.index');
    }

    /**
     * Phương thức update cho Route::resource.
     */
    public function update(Request $request, StoreLocation $storeLocation)
    {
        return $this->store($request);
    }

    /**
     * Xóa mềm một cửa hàng.
     */
    public function destroy(StoreLocation $storeLocation)
    {
        $storeLocation->delete();
        return response()->json(['message' => 'Cửa hàng đã được xóa mềm thành công!']);
    }

    /**
     * Thay đổi trạng thái kích hoạt của cửa hàng.
     */
    public function toggleActive(Request $request, StoreLocation $storeLocation)
    {
        $storeLocation->is_active = !$storeLocation->is_active;
        $storeLocation->save();

        return response()->json([
            'message' => 'Trạng thái cửa hàng đã được cập nhật.',
            'is_active' => $storeLocation->is_active
        ]);
    }

    /**
     * Lấy thông tin chi tiết một cửa hàng để điền vào form chỉnh sửa.
     */
    public function edit(StoreLocation $storeLocation)
    {
        // Hãy thử cách này trước:
    $data = $storeLocation->toArray();

    // Nếu bạn muốn bao gồm tên của Tỉnh/Huyện/Xã cho mục đích hiển thị khác
    // mà không phải chỉ là code:
    $data['province'] = $storeLocation->province ? $storeLocation->province->toArray() : null;
    $data['district'] = $storeLocation->district ? $storeLocation->district->toArray() : null;
    $data['ward'] = $storeLocation->ward ? $storeLocation->ward->toArray() : null;

    return response()->json($data);
    }
    /**
     * Lấy danh sách các cửa hàng đã bị xóa mềm.
     */
    public function trashed()
    {
        $trashedLocations = StoreLocation::onlyTrashed() // Chỉ lấy các bản ghi đã xóa mềm
                                        ->with(['province', 'district', 'ward'])
                                        ->orderBy('deleted_at', 'desc')
                                        ->get();
                                        //  dd($trashedLocations->toArray());

        return view('admin.store_locations.trashed', compact('trashedLocations'));
    }

    /**
     * Khôi phục một cửa hàng đã xóa mềm.
     */
    public function restore($id)
    {
        $storeLocation = StoreLocation::onlyTrashed()->findOrFail($id);
        $storeLocation->restore(); // Khôi phục bản ghi

        Session::flash('success', 'Cửa hàng đã được khôi phục thành công!');
        return redirect()->route('admin.store-locations.trashed');
    }

    /**
     * Xóa vĩnh viễn một cửa hàng.
     */
    public function forceDelete($id)
    {
        $storeLocation = StoreLocation::onlyTrashed()->findOrFail($id);
        $storeLocation->forceDelete(); // Xóa vĩnh viễn bản ghi khỏi database

        Session::flash('success', 'Cửa hàng đã được xóa vĩnh viễn thành công!');
        return redirect()->route('admin.store-locations.trashed');
    }

    // ... (Bạn có thể bỏ qua phần này, nó đã được đề cập trong các giải thích trước)
    /**
     * Lấy thông tin chi tiết một cửa hàng để điền vào form chỉnh sửa.
     */
    // public function edit(StoreLocation $storeLocation)
    // {
    //     // Đảm bảo các code địa chỉ được bao gồm trong response JSON
    //     $data = $storeLocation->toArray();
    //     $data['province'] = $storeLocation->province ? $storeLocation->province->toArray() : null;
    //     $data['district'] = $storeLocation->district ? $storeLocation->district->toArray() : null;
    //     $data['ward'] = $storeLocation->ward ? $storeLocation->ward->toArray() : null;

    //     return response()->json($data);
    // }
}
