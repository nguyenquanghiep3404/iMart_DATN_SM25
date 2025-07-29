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
        $locations = StoreLocation::all();

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
        ];
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:registers,name,' . $request->id,
            'store_location_id' => 'required|exists:store_locations,id',
            'device_uid' => 'nullable|numeric',
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
}
