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
use App\Http\Requests\SalesStaffRequest;
use App\Http\Requests\WorkShiftRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
class SalesStaffManagement extends Controller
{
    /**
     * Hiển thị trang quản lý nhân viên bán hàng - Danh sách cửa hàng
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10);
        $stores = StoreLocation::with(['assignedUsers', 'province', 'district'])
            ->where('type', 'store')
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
        session(['last_store_id' => $storeId]);
        $perPage = $request->get('per_page', 10);
        // Lấy danh sách nhân viên với phân trang
        $employeeAssignments = UserStoreLocation::with(['user'])
            ->where('store_location_id', $storeId)
            ->whereHas('user') // Chỉ lấy các record có user tồn tại
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
        $employees = new LengthAwarePaginator(
            $employees,
            $employeeAssignments->total(),
            $employeeAssignments->perPage(),
            $employeeAssignments->currentPage(),
            [
                'path' => Paginator::resolveCurrentPath(),
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
            ->whereHas('user') // Chỉ lấy các record có user tồn tại
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
            ->where('type', 'store')
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
            ->where('store_location_id', $storeId)
            ->whereHas('user'); // Chỉ lấy các record có user tồn tại
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
        $employee = UserStoreLocation::with(['user', 'storeLocation.province', 'storeLocation.district'])
            ->where('store_location_id', $storeId)
            ->where('user_id', $employeeId)
            ->whereHas('user') // Chỉ lấy các record có user tồn tại
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
                'store_location_id' => $employee->store_location_id,
                'store_location' => [
                    'id' => $employee->storeLocation->id,
                    'name' => $employee->storeLocation->name,
                    'province' => [
                        'code' => $employee->storeLocation->province->code ?? null,
                        'name' => $employee->storeLocation->province->name_with_type ?? null,
                    ],
                    'district' => [
                        'code' => $employee->storeLocation->district->code ?? null,
                        'name' => $employee->storeLocation->district->name_with_type ?? null,
                    ],
                ]
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

        try {
            DB::transaction(function () use ($userId, $data) {
                $user = User::findOrFail($userId);
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone_number' => $data['phone'],
                    'status' => $data['status'] ?? 'active',
                ]);
                // Cập nhật store_location nếu có thay đổi
                if (isset($data['store_location_id']) && $data['store_location_id']) {
                    UserStoreLocation::where('user_id', $userId)->delete();
                    UserStoreLocation::ganNhanVienVaoCuaHang($userId, $data['store_location_id']);
                }
            });
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
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
            // Lấy user để xóa mềm
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'Không tìm thấy nhân viên'], 404);
            }

            // Xóa mềm user (chuyển vào thùng rác)
            $user->delete();

            // Xóa lịch làm việc của nhân viên tại cửa hàng này
            EmployeeSchedule::where('user_id', $userId)
                ->where('store_location_id', $storeId)
                ->delete();
            return response()->json(['message' => 'Đã chuyển nhân viên vào thùng rác thành công']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa nhân viên: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Hiển thị trang thùng rác - danh sách nhân viên đã xóa mềm
     */
    public function trash(): View
    {
        $trashedUsers = User::onlyTrashed()
            ->whereHas('assignedStoreLocations') // Chỉ lấy những user đã từng được gán vào cửa hàng
            ->with(['assignedStoreLocations.province'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.Salesperson.trash', compact('trashedUsers'));
    }
    /**
     * Khôi phục nhân viên đã xóa mềm
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.sales-staff.trash')
            ->with('success', "Đã khôi phục nhân viên '{$user->name}' thành công!");
    }
    /**
     * Xóa vĩnh viễn nhân viên khỏi database
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $userName = $user->name;
        // Xóa các liên kết với cửa hàng trước khi xóa vĩnh viễn
        UserStoreLocation::where('user_id', $id)->delete();
        // Xóa lịch làm việc
        EmployeeSchedule::where('user_id', $id)->delete();
        // Xóa vĩnh viễn user
        $user->forceDelete();
        return redirect()->route('admin.sales-staff.trash')
            ->with('success', "Đã xóa vĩnh viễn nhân viên '{$userName}'.");
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
     * API: Lấy thông tin chi tiết ca làm việc
     */
    public function getWorkShift(int $workShiftId): JsonResponse
    {
        $workShift = WorkShift::findOrFail($workShiftId);
        return response()->json(['work_shift' => $workShift]);
    }
    /**
     * API: Thêm ca làm việc mới
     */
    public function addWorkShift(WorkShiftRequest $request): JsonResponse
    {
        try {
            $workShift = WorkShift::create([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'color_code' => $request->color_code,
            ]);

            return response()->json([
                'message' => 'Thêm ca làm việc thành công',
                'work_shift' => $workShift
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi thêm ca làm việc'
            ], 500);
        }
    }

    /**
     * API: Cập nhật ca làm việc
     */
    public function updateWorkShift(int $workShiftId, WorkShiftRequest $request): JsonResponse
    {
        try {
            $workShift = WorkShift::findOrFail($workShiftId);
            $workShift->update([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'color_code' => $request->color_code,
            ]);
            return response()->json([
                'message' => 'Cập nhật ca làm việc thành công',
                'work_shift' => $workShift
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật ca làm việc'
            ], 500);
        }
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
