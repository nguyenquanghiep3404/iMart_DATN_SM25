<?php

namespace App\Http\Controllers\Admin;
use App\Models\StoreLocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Register;
class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = Register::with('storeLocation');

        // Tìm kiếm theo tên hoặc device_uid
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('device_uid', 'like', "%{$search}%");
            });
        }

        // Lọc theo cửa hàng
        if ($locationId = $request->input('location_id')) {
            if ($locationId !== 'all') {
                $query->where('store_location_id', $locationId);
            }
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status); // Nếu cột là `is_active`, sửa lại cho đúng
            }
        }

        $registers = $query->get();
        $locations = StoreLocation::with(['province', 'district'])->get()->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'store_location_name' => $location->name,
                'province_name' => $location->province?->name_with_type ?? null,
                'district_name' => $location->district?->name_with_type ?? null,
            ];
        });
        
        
        // dd($locations);

        return view('admin.registers.index', [
            'registers' => $registers,
            'locations' => $locations,
            'filters' => [
                'search' => $search ?? '',
                'location_id' => $locationId ?? 'all',
                'status' => $status ?? 'all',
            ],
        ]);
    }
    public function save(Request $request)
    {
        $messages = [
            'name.unique' => 'Tên máy đã tồn tại, vui lòng chọn tên khác.',
            'name.required' => 'Tên máy là trường bắt buộc.',
            'device_uid.required' => 'Device UID là trường bắt buộc.',
            'device_uid.numeric' => 'Device UID phải là số.',
        ];
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:registers,name,' . $request->id,
            'store_location_id' => 'required|exists:store_locations,id',
            'device_uid' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ],$messages);
    
        if ($request->filled('id')) {
            // CẬP NHẬT
            $register = Register::findOrFail($request->id);
            $register->update($validated);
        } else {
            // THÊM MỚI
            $register = Register::create($validated);
        }
        $register->load('storeLocation');
        $data = $register->toArray();
        $data['store_location_name'] = $register->storeLocation->name ?? 'N/A';
    
        return response()->json([
            'message' => $request->filled('id') ? 'Cập nhật thành công' : 'Thêm thành công',
            'data' => $data,
        ]);
    }
    public function destroy($id)
    {
        try {
            $register = Register::findOrFail($id);
            $register->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xoá máy POS thành công!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi xoá máy POS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xoá máy POS.',
            ], 500);
        }
    }

    public function trashed(Request $request)
    {
        $query = Register::onlyTrashed()->with('storeLocation');

        // Tìm kiếm theo tên hoặc device_uid (nếu có)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('device_uid', 'like', "%{$search}%");
            });
        }

        // Lọc theo cửa hàng (nếu có)
        if ($locationId = $request->input('location_id')) {
            if ($locationId !== 'all') {
                $query->where('store_location_id', $locationId);
            }
        }

        $registers = $query->get();

        $locations = StoreLocation::with(['province', 'district'])->get()->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'store_location_name' => $location->name,
                'province_name' => $location->province?->name_with_type ?? null,
                'district_name' => $location->district?->name_with_type ?? null,
            ];
        });

        return view('admin.registers.trashed', [
            'registers' => $registers,
            'locations' => $locations,
            'filters' => [
                'search' => $search ?? '',
                'location_id' => $locationId ?? 'all',
            ],
        ]);
    }

    public function restore($id)
    {
        try {
            // Lấy bản ghi đã bị xóa mềm (chỉ trong thùng rác)
            $register = Register::onlyTrashed()->findOrFail($id);

            // Khôi phục bản ghi (xóa giá trị deleted_at)
            $register->restore();

            return response()->json([
                'success' => true,
                'message' => 'Khôi phục máy POS thành công!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khôi phục máy POS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi khôi phục máy POS.',
            ], 500);
        }
    }
    public function forceDelete($id)
    {
        try {
            // Lấy bản ghi soft deleted mới có thể xóa vĩnh viễn
            $register = Register::onlyTrashed()->findOrFail($id);

            $register->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn máy POS thành công.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi xóa vĩnh viễn máy POS ID: ' . $id . ' - ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa vĩnh viễn máy POS.',
            ], 500);
        }
    }
}
