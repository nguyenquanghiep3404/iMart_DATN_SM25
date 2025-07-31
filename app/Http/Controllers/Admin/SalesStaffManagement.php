<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\User;
use App\Models\StoreLocation;
use App\Models\UserStoreLocation;
use App\Models\WorkShift;
use App\Models\EmployeeSchedule;
use App\Models\ProvinceOld;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SalesStaffRequest;

class SalesStaffManagement extends Controller
{
    /**
     * Hiển thị trang quản lý nhân viên bán hàng - Danh sách cửa hàng
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10); // Số item trên mỗi trang, mặc định 10
        
        $stores = StoreLocation::with(['assignedUsers', 'province', 'district'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);

        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();

        return view('admin.Salesperson.index', compact('stores', 'provinces'));
    }

    /**
     * Hiển thị danh sách nhân viên của một cửa hàng
     */
    public function showEmployees(int $storeId, Request $request): View
    {
        $store = StoreLocation::with(['province', 'district'])->findOrFail($storeId);
        
        $perPage = $request->get('per_page', 10); // Số item trên mỗi trang, mặc định 10
        
        // Lấy danh sách nhân viên với phân trang
        $employeeAssignments = UserStoreLocation::with(['user'])
            ->where('store_location_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Chuyển đổi dữ liệu cho view
        $employees = $employeeAssignments->getCollection()->map(function ($assignment) {
            $user = $assignment->user;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'position' => $user->position ?? 'Nhân viên POS',
                'status' => $user->status,
            ];
        });
        
        // Tạo paginator mới với dữ liệu đã chuyển đổi
        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employees,
            $employeeAssignments->total(),
            $employeeAssignments->perPage(),
            $employeeAssignments->currentPage(),
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();

        return view('admin.Salesperson.employees', compact('store', 'employees', 'provinces'));
    }

    /**
     * Hiển thị lịch làm việc của cửa hàng
     */
    public function showSchedule(int $storeId, Request $request): View
    {
        $store = StoreLocation::findOrFail($storeId);
        
        // Lấy tuần hiện tại hoặc tuần được chọn
        $weekStartDate = $request->get('week_start', Carbon::now()->startOfWeek());
        $weekStartDate = Carbon::parse($weekStartDate);
        
        // Lấy nhân viên của cửa hàng
        $employees = UserStoreLocation::with(['user'])
            ->where('store_location_id', $storeId)
            ->get()
            ->pluck('user');

        // Lấy lịch làm việc của tuần
        $schedules = EmployeeSchedule::layLichLamViecTuanCuaCuaHang($storeId, $weekStartDate->format('Y-m-d'));

        // Lấy danh sách ca làm việc
        $workShifts = WorkShift::all();

        return view('admin.Salesperson.schedule', compact('store', 'employees', 'schedules', 'workShifts', 'weekStartDate'));
    }

    /**
     * Hiển thị trang quản lý ca làm việc
     */
    public function showWorkShifts(): View
    {
        $workShifts = WorkShift::all();
        return view('admin.Salesperson.work_shifts', compact('workShifts'));
    }

    /**
     * API: Lấy danh sách cửa hàng với filter
     */
    public function getStores(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        
        $query = StoreLocation::with(['assignedUsers', 'province', 'district'])
            ->where('is_active', true);

        // Filter theo tỉnh/thành
        if ($request->filled('province')) {
            $query->where('province_code', $request->province);
        }

        // Filter theo quận/huyện
        if ($request->filled('district')) {
            $query->where('district_code', $request->district);
        }

        // Search theo tên cửa hàng
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $stores = $query->orderBy('name')->paginate($perPage);
        
        $storesData = $stores->getCollection()->map(function ($store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'province' => $store->province->name_with_type ?? '',
                'district' => $store->district->name_with_type ?? '',
                'staff_count' => $store->assignedUsers->count(),
            ];
        });

        return response()->json([
            'stores' => $storesData,
            'pagination' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
                'from' => $stores->firstItem(),
                'to' => $stores->lastItem(),
            ]
        ]);
    }

    /**
     * API: Lấy danh sách nhân viên của cửa hàng
     */
    public function getStoreEmployees(int $storeId, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        
        $query = UserStoreLocation::with(['user'])
            ->where('store_location_id', $storeId);

        // Search theo tên, email, số điện thoại
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $employeeAssignments = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        $employees = $employeeAssignments->getCollection()->map(function ($assignment) {
            $user = $assignment->user;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'position' => $user->position ?? 'Nhân viên POS',
                'status' => $user->status,
            ];
        });

        return response()->json([
            'employees' => $employees,
            'pagination' => [
                'current_page' => $employeeAssignments->currentPage(),
                'last_page' => $employeeAssignments->lastPage(),
                'per_page' => $employeeAssignments->perPage(),
                'total' => $employeeAssignments->total(),
                'from' => $employeeAssignments->firstItem(),
                'to' => $employeeAssignments->lastItem(),
            ]
        ]);
    }

    /**
     * API: Lấy thông tin một nhân viên cụ thể
     */
    public function getEmployee(int $storeId, int $employeeId): JsonResponse
    {
        $employee = UserStoreLocation::with(['user'])
            ->where('store_location_id', $storeId)
            ->where('user_id', $employeeId)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhân viên'
            ], 404);
        }

        $user = $employee->user;
        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'status' => $user->status,
            ]
        ]);
    }

    /**
     * API: Thêm nhân viên vào cửa hàng
     */
    public function addEmployee(SalesStaffRequest $request): JsonResponse
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone'],
                'status' => $data['status'] ?? 'active',
                'password' => bcrypt('password123'),
            ]);
            UserStoreLocation::ganNhanVienVaoCuaHang($user->id, $data['store_location_id']);
            DB::commit();
            return response()->json(['message' => 'Thêm nhân viên thành công!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'users_phone_number_unique')) {
                return response()->json(['errors' => ['phone' => ['Số điện thoại đã tồn tại trong hệ thống.']]], 422);
            }
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Cập nhật thông tin nhân viên
     */
    public function updateEmployee(int $userId, SalesStaffRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::findOrFail($userId);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone'],
            'status' => $data['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'Cập nhật nhân viên thành công',
            'user' => $user
        ]);
    }

    /**
     * API: Xóa nhân viên khỏi cửa hàng
     */
    public function removeEmployee(int $storeId, int $userId): JsonResponse
    {
        try {
            // Debug: Log thông tin để kiểm tra
            \Log::info('Removing employee', [
                'storeId' => $storeId,
                'userId' => $userId,
                'userExists' => User::find($userId) ? 'Yes' : 'No',
                'storeExists' => StoreLocation::find($storeId) ? 'Yes' : 'No',
                'assignmentExists' => UserStoreLocation::where('user_id', $userId)
                    ->where('store_location_id', $storeId)
                    ->exists(),
                'nhanVienThuocCuaHang' => UserStoreLocation::nhanVienThuocCuaHang($userId, $storeId)
            ]);
            
            // Kiểm tra nhân viên có thuộc cửa hàng không
            $assignmentExists = UserStoreLocation::where('user_id', $userId)
                ->where('store_location_id', $storeId)
                ->exists();
                
            if (!$assignmentExists) {
                return response()->json(['message' => 'Nhân viên không thuộc cửa hàng này'], 404);
            }

            // Xóa lịch làm việc của nhân viên tại cửa hàng này
            EmployeeSchedule::where('user_id', $userId)
                ->where('store_location_id', $storeId)
                ->delete();

            // Xóa liên kết nhân viên với cửa hàng
            UserStoreLocation::xoaNhanVienKhoiCuaHang($userId, $storeId);

            return response()->json(['message' => 'Xóa nhân viên khỏi cửa hàng thành công']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa nhân viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy lịch làm việc theo tuần
     */
    public function getWeeklySchedule(int $storeId, Request $request): JsonResponse
    {
        $weekStartDate = Carbon::parse($request->get('week_start', Carbon::now()->startOfWeek()));
        
        $schedules = EmployeeSchedule::layLichLamViecTuanCuaCuaHang($storeId, $weekStartDate->format('Y-m-d'));

        return response()->json([
            'schedules' => $schedules,
            'week_start' => $weekStartDate->format('Y-m-d')
        ]);
    }

    /**
     * API: Gán ca làm việc cho nhân viên
     */
    public function assignShift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'store_location_id' => 'required|exists:store_locations,id',
            'work_shift_name' => 'required|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra nhân viên có thuộc cửa hàng không
        if (!UserStoreLocation::nhanVienThuocCuaHang($request->user_id, $request->store_location_id)) {
            return response()->json(['message' => 'Nhân viên không thuộc cửa hàng này'], 404);
        }

        try {
            if ($request->work_shift_name === 'Nghỉ') {
                // Xóa ca làm việc nếu chọn "Nghỉ"
                EmployeeSchedule::xoaCa($request->user_id, $request->date);
                $message = 'Đã xóa ca làm việc';
            } else {
                // Gán ca làm việc
                EmployeeSchedule::ganCaTheoTen(
                    $request->user_id,
                    $request->store_location_id,
                    $request->work_shift_name,
                    $request->date,
                    auth()->id()
                );
                $message = 'Gán ca làm việc thành công';
            }

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy danh sách ca làm việc
     */
    public function getWorkShifts(): JsonResponse
    {
        $workShifts = WorkShift::all();
        return response()->json(['work_shifts' => $workShifts]);
    }

    /**
     * API: Thêm ca làm việc mới
     */
    public function addWorkShift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:work_shifts,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'color_code' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $workShift = WorkShift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'color_code' => $request->color_code ?? '#4299E1',
        ]);

        return response()->json([
            'message' => 'Thêm ca làm việc thành công',
            'work_shift' => $workShift
        ], 201);
    }

    /**
     * API: Cập nhật ca làm việc
     */
    public function updateWorkShift(int $workShiftId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:work_shifts,name,' . $workShiftId,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'color_code' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $workShift = WorkShift::findOrFail($workShiftId);
        $workShift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'color_code' => $request->color_code ?? $workShift->color_code,
        ]);

        return response()->json([
            'message' => 'Cập nhật ca làm việc thành công',
            'work_shift' => $workShift
        ]);
    }

    /**
     * API: Xóa ca làm việc
     */
    public function deleteWorkShift(int $workShiftId): JsonResponse
    {
        $workShift = WorkShift::findOrFail($workShiftId);

        // Kiểm tra xem ca làm việc có đang được sử dụng không
        $isUsed = EmployeeSchedule::where('work_shift_id', $workShiftId)->exists();
        
        if ($isUsed) {
            return response()->json(['message' => 'Không thể xóa ca làm việc đang được sử dụng'], 400);
        }

        $workShift->delete();

        return response()->json(['message' => 'Xóa ca làm việc thành công']);
    }

    /**
     * API: Tạo ca làm việc mặc định
     */
    public function createDefaultWorkShifts(): JsonResponse
    {
        try {
            WorkShift::taoCaLamViecMacDinh();
            return response()->json(['message' => 'Tạo ca làm việc mặc định thành công']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy thống kê nhân viên theo cửa hàng
     */
    public function getStaffStatistics(): JsonResponse
    {
        $statistics = StoreLocation::with(['assignedUsers'])
            ->where('is_active', true)
            ->get()
            ->map(function ($store) {
                return [
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'total_staff' => $store->assignedUsers->count(),
                    'active_staff' => $store->assignedUsers->where('status', 'active')->count(),
                    'inactive_staff' => $store->assignedUsers->where('status', 'inactive')->count(),
                ];
            });

        return response()->json(['statistics' => $statistics]);
    }
}
