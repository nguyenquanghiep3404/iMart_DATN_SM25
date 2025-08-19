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

        // Debug: Log số lượng dữ liệu
        \Log::info('StoreLocationController::index - Found ' . $storeLocations->count() . ' locations');
        \Log::info('StoreLocationController::index - Found ' . $provinces->count() . ' provinces');

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

        try {
            $validatedData = $request->validate($rules);

            if ($id) {
                $storeLocation = StoreLocation::findOrFail($id);
                $storeLocation->update($validatedData);
                $message = 'Cửa hàng đã được cập nhật thành công!';
            } else {
                $storeLocation = StoreLocation::create($validatedData);
                $message = 'Cửa hàng đã được thêm mới thành công!';
            }

            // Trả về JSON thay vì redirect
            return response()->json(['message' => $message, 'status' => 'success', 'location' => $storeLocation]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu.',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi server: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }

    /**
     * Phương thức update cho Route::resource.
     */
    public function update(Request $request, StoreLocation $storeLocation)
    {
        // Khi sử dụng AJAX PUT, $storeLocation sẽ được tự động resolve.
        // Cập nhật trường 'id' trong request để phương thức 'store' có thể xử lý.
        $request->merge(['id' => $storeLocation->id]);
        return $this->store($request);
    }

    /**
     * Xóa mềm một cửa hàng.
     */
    public function destroy(StoreLocation $storeLocation)
    {
        try {
            $storeLocation->delete();
            return response()->json(['message' => 'Cửa hàng đã được xóa mềm thành công!', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa mềm địa điểm: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }

    /**
     * Thay đổi trạng thái kích hoạt của cửa hàng.
     */
    public function toggleActive(Request $request, StoreLocation $storeLocation)
    {
        try {
            $storeLocation->is_active = !$storeLocation->is_active;
            $storeLocation->save();

            return response()->json([
                'message' => 'Trạng thái cửa hàng đã được cập nhật.',
                'is_active' => $storeLocation->is_active,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết một cửa hàng để điền vào form chỉnh sửa.
     */
    public function edit(StoreLocation $storeLocation)
    {
        $storeLocation->load(['province', 'district', 'ward']);
        return response()->json($storeLocation);
    }

    /**
     * Lấy danh sách các cửa hàng (không bao gồm các cửa hàng đã xóa mềm) cho API.
     */
    public function apiIndex()
    {
        $storeLocations = StoreLocation::with(['province', 'district', 'ward'])
                                       ->orderBy('id', 'desc')
                                       ->get();
        return response()->json($storeLocations);
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

        return view('admin.store_locations.trashed', compact('trashedLocations'));
    }

    /**
     * Khôi phục một cửa hàng đã xóa mềm.
     */
    public function restore($id)
    {
        try {
            $storeLocation = StoreLocation::onlyTrashed()->findOrFail($id);
            $storeLocation->restore(); // Khôi phục bản ghi

            // Trả về JSON thay vì redirect
            return response()->json(['message' => 'Cửa hàng đã được khôi phục thành công!', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi khôi phục địa điểm: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn một cửa hàng.
     */
    public function forceDelete($id)
    {
        try {
            $storeLocation = StoreLocation::onlyTrashed()->findOrFail($id);
            $storeLocation->forceDelete(); // Xóa vĩnh viễn bản ghi khỏi database

            // Trả về JSON thay vì redirect
            return response()->json(['message' => 'Cửa hàng đã được xóa vĩnh viễn thành công!', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa vĩnh viễn địa điểm: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }
}
